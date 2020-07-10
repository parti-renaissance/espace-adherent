<?php

namespace App\Command;

use App\Entity\ReferentTag;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailchimpCreateTagFromReferentTagCommand extends AbstractMailchimpReferentTagSegmentCommand
{
    protected static $defaultName = 'mailchimp:sync:referent-tag';

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Sync Referent tag with Mailchimp (create Mailchimp tag)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $input->getArgument('list');

        $url = sprintf('/3.0/lists/%s/segments', $this->getListId($list));

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
                    switch ($list) {
                        case self::LIST_MAIN:
                            $tag->setExternalId($data['id']);

                            break;
                        case self::LIST_ELECTED_REPRESENTATIVE:
                            $tag->setExternalElectedRepresentativeListId($data['id']);

                            break;
                        default:
                            break;
                    }
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
