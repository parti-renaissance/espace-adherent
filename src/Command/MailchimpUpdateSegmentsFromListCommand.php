<?php

namespace App\Command;

use App\Entity\MailchimpSegment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailchimpUpdateSegmentsFromListCommand extends AbstractMailchimpReferentTagSegmentCommand
{
    protected static $defaultName = 'mailchimp:sync:segments';

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Sync segments of a given list.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $input->getArgument('list');

        $this->io->progressStart();

        $offset = 0;
        $limit = 1000;

        $this->entityManager->beginTransaction();

        try {
            while ($segments = $this->getSegments($list, $offset, $limit)) {
                foreach ($segments as $segment) {
                    $this->updateSegment($segment, $list);
                }

                $this->entityManager->flush();

                $offset += $limit;
            }
        } catch (\Exception $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }

        $this->io->progressFinish();
    }

    private function updateSegment(array $segment, string $list): void
    {
        $label = $segment['label'];
        $externalId = $segment['id'];

        if ($segment = $this->findSegment($list, $label)) {
            $segment->setExternalId($externalId);

            return;
        }

        $this->entityManager->persist(new MailchimpSegment($list, $label, $externalId));
    }
}
