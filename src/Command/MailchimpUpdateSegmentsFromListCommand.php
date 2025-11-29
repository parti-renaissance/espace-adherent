<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\MailchimpSegment;
use App\Mailchimp\Driver;
use App\Repository\MailchimpSegmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mailchimp:sync:segments',
    description: 'Sync segments of a given list.',
)]
class MailchimpUpdateSegmentsFromListCommand extends Command
{
    private $entityManager;
    private $driver;
    private $segmentRepository;
    private $mailchimpMainListId;
    private $mailchimpElectedRepresentativeListId;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailchimpSegmentRepository $segmentRepository,
        Driver $driver,
        string $mailchimpListId,
        string $mailchimpElectedRepresentativeListId,
    ) {
        $this->entityManager = $entityManager;
        $this->driver = $driver;
        $this->segmentRepository = $segmentRepository;
        $this->mailchimpMainListId = $mailchimpListId;
        $this->mailchimpElectedRepresentativeListId = $mailchimpElectedRepresentativeListId;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('list', InputArgument::REQUIRED, implode('|', MailchimpSegment::LISTS))
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = $input->getArgument('list');
        $listId = $this->getListId($list);

        $this->io->progressStart();

        $offset = 0;
        $limit = 1000;

        $this->entityManager->beginTransaction();

        try {
            while ($segments = $this->driver->getSegments($listId, $offset, $limit)) {
                foreach ($segments as $segment) {
                    $this->updateSegment($segment, $list);
                }

                $this->entityManager->flush();

                $this->io->progressAdvance();

                $offset += $limit;
            }

            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function findSegment(string $list, string $label): ?MailchimpSegment
    {
        return $this->segmentRepository->findOneForListByLabel($list, $label);
    }

    private function updateSegment(array $segment, string $list): void
    {
        $label = $segment['name'];
        $externalId = $segment['id'];

        if ($segment = $this->findSegment($list, $label)) {
            $segment->setExternalId($externalId);

            return;
        }

        $this->entityManager->persist(MailchimpSegment::createElectedRepresentativeSegment($label, $externalId));
    }

    private function getListId(string $list): string
    {
        switch ($list) {
            case MailchimpSegment::LIST_MAIN:
                return $this->mailchimpMainListId;
            case MailchimpSegment::LIST_ELECTED_REPRESENTATIVE:
                return $this->mailchimpElectedRepresentativeListId;
            default:
                throw new \InvalidArgumentException(\sprintf('List "%s"" is invalid. Available lists are: "%s".', $list, implode('", "', MailchimpSegment::LISTS)));
        }
    }
}
