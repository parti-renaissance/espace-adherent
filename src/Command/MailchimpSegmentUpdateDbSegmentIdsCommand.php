<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'mailchimp:segment:update-db-segment-ids',
    description: 'Update Referent tags with Mailchimp external ids',
)]
class MailchimpSegmentUpdateDbSegmentIdsCommand extends Command
{
    private $committeeRepository;
    private $client;
    private $entityManager;
    private $mailchimpListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        CommitteeRepository $committeeRepository,
        HttpClientInterface $mailchimpClient,
        ObjectManager $entityManager,
        string $mailchimpListId,
    ) {
        $this->committeeRepository = $committeeRepository;
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpListId = $mailchimpListId;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->progressStart();

        $offset = 0;
        $limit = 1000;

        while ($tags = $this->getTags($offset, $limit)) {
            $this->updateCommittees($tags);

            $offset += $limit;
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function updateCommittees(array $segments): void
    {
        $iterator = $this->committeeRepository->createQueryBuilder('committee')
            ->where('committee.mailchimpId IS NULL')
            ->andWhere('committee.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $committee) {
            /** @var Committee $committee */
            $committee = current($committee);

            foreach ($segments as $tag) {
                if ($tag['name'] === $committee->getUuid()->toString()) {
                    $committee->setMailchimpId($tag['id']);

                    $this->io->progressAdvance();
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function getTags(int $offset, int $limit): array
    {
        $params = [
            'query' => [
                'offset' => $offset,
                'count' => $limit,
                'fields' => 'segments.id,segments.name',
            ],
        ];

        $response = $this->client->request('GET', \sprintf('/3.0/lists/%s/segments', $this->mailchimpListId), $params);

        if (200 !== $response->getStatusCode()) {
            return [];
        }

        return $response->toArray()['segments'];
    }
}
