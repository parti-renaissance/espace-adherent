<?php

namespace App\Command;

use App\Entity\ReferentTag;
use App\Repository\ReferentTagRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpCreateTagFromReferentTagCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:referent-tag';

    private $referentTagRepository;
    private $client;
    private $entityManager;
    private $mailchimpListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ReferentTagRepository $referentTagRepository,
        ClientInterface $mailchimpClient,
        ObjectManager $entityManager,
        string $mailchimpListId
    ) {
        $this->referentTagRepository = $referentTagRepository;
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpListId = $mailchimpListId;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Sync Referent tag with Mailchimp (create Mailchimp tag)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = sprintf('/3.0/lists/%s/segments', $this->mailchimpListId);

        $referentTags = $this->referentTagRepository->findBy(['externalId' => null]);
        $this->io->progressStart($countAllTags = \count($referentTags));

        $countSyncTags = 0;

        /** @var ReferentTag $tag */
        foreach ($referentTags as $tag) {
            try {
                $response = $this->client->request('POST', $url, ['json' => [
                    'name' => $tag->getCode(),
                    'static_segment' => [],
                ]]);
            } catch (RequestException $e) {
                $this->io->warning($e->getRequest() ? (string) $e->getResponse()->getBody() : $e->getMessage());
                $this->io->progressAdvance();
                continue;
            }

            if (200 === $response->getStatusCode()) {
                $data = json_decode((string) $response->getBody(), true);

                if (isset($data['id'])) {
                    $tag->setExternalId($data['id']);
                    $this->entityManager->flush();
                    ++$countSyncTags;
                }
            }
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->note(sprintf('Synchronized %d/%d tags', $countSyncTags, $countAllTags));
    }
}
