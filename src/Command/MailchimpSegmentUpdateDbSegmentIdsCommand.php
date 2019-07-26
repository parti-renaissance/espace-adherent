<?php

namespace AppBundle\Command;

use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpSegmentUpdateDbSegmentIdsCommand extends Command
{
    protected static $defaultName = 'mailchimp:segment:update-db-segment-ids';

    private $referentTagRepository;
    private $committeeRepository;
    private $citizenProjectRepository;
    private $client;
    private $entityManager;
    private $mailchimpListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ReferentTagRepository $referentTagRepository,
        CommitteeRepository $committeeRepository,
        CitizenProjectRepository $citizenProjectRepository,
        ClientInterface $mailchimpClient,
        ObjectManager $entityManager,
        string $mailchimpListId
    ) {
        $this->referentTagRepository = $referentTagRepository;
        $this->committeeRepository = $committeeRepository;
        $this->citizenProjectRepository = $citizenProjectRepository;
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpListId = $mailchimpListId;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Update Referent tags with Mailchimp external ids');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->progressStart();

        $offset = 0;
        $limit = 1000;

        while ($tags = $this->getTags($offset, $limit)) {
            $this->updateReferentTags($tags);
            $this->updateCommittees($tags);
            $this->updateCitizenProjects($tags);

            $offset += $limit;
        }

        $this->io->progressFinish();
    }

    private function updateReferentTags(array $segments): void
    {
        $iterator = $this->referentTagRepository->createQueryBuilder('tag')
            ->where('tag.externalId IS NULL')
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $refTag) {
            $refTag = current($refTag);

            foreach ($segments as $tag) {
                if ($tag['name'] === $refTag->getCode()) {
                    $refTag->setExternalId($tag['id']);
                    $this->io->progressAdvance();
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
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

    private function updateCitizenProjects(array $segments): void
    {
        $iterator = $this->citizenProjectRepository->createQueryBuilder('cp')
            ->where('cp.mailchimpId IS NULL')
            ->andWhere('cp.status = :status')
            ->setParameter('status', CitizenProject::APPROVED)
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $cp) {
            /** @var CitizenProject $cp */
            $cp = current($cp);

            foreach ($segments as $tag) {
                if ($tag['name'] === $cp->getUuid()->toString()) {
                    $cp->setMailchimpId($tag['id']);

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

        $response = $this->client->request('GET', sprintf('/3.0/lists/%s/segments', $this->mailchimpListId), $params);

        if (200 !== $response->getStatusCode()) {
            return [];
        }

        return json_decode((string) $response->getBody(), true)['segments'];
    }
}
