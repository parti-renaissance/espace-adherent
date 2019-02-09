<?php

namespace AppBundle\Command;

use AppBundle\Entity\ReferentTag;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpUpdateReferentTagExternalIdFromMailchimpCommand extends Command
{
    protected static $defaultName = 'mailchimp:update:referent-tag-external-id';

    private $referentTagRepository;
    private $client;
    private $entityManager;
    private $mailchimpListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ReferentTagRepository $referentTagRepository,
        ClientInterface $client,
        ObjectManager $entityManager,
        string $mailchimpListId
    ) {
        $this->referentTagRepository = $referentTagRepository;
        $this->client = $client;
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
        $url = sprintf('/3.0/lists/%s/segments?count=2000', $this->mailchimpListId);

        /** @var ReferentTag[] $referentTags */
        $referentTags = $this->referentTagRepository->findBy(['externalId' => null]);
        $this->io->progressStart($countAllTags = \count($referentTags));

        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            return;
        }

        $tags = json_decode((string) $response->getBody(), true);

        foreach ($referentTags as $refTag) {
            $this->io->progressAdvance();

            foreach ($tags['segments'] as $tag) {
                if ($tag['name'] === $refTag->getCode()) {
                    $refTag->setExternalId($tag['id']);
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->io->progressFinish();
    }
}
