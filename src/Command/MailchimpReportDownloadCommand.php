<?php

namespace App\Command;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpCampaignReport;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsCommand(
    name: 'mailchimp:report:download',
    description: 'Download Mailchimp campaigns reports',
)]
class MailchimpReportDownloadCommand extends Command
{
    private $repository;
    private $entityManager;
    /** @var SymfonyStyle */
    private $io;
    private $manager;
    private $propertyAccessor;

    public function __construct(
        ObjectManager $entityManager,
        MailchimpCampaignRepository $mailchimpCampaignRepository,
        Manager $manager,
        PropertyAccessorInterface $propertyAccessor,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $mailchimpCampaignRepository;
        $this->manager = $manager;
        $this->propertyAccessor = $propertyAccessor;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('recent-only', null, InputOption::VALUE_NONE, 'Download campaign reports for only recent messages')
            ->addOption('recent-interval', null, InputOption::VALUE_REQUIRED, 'Duration of recent interval in day (default: 14 days)', 14)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = null;

        if ($input->getOption('recent-only')) {
            $from = (new \DateTime())->modify(\sprintf('-%d days', (int) $input->getOption('recent-interval')));
        }

        $paginator = $this->createPaginator($from);

        $this->io->progressStart($total = $paginator->count());
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $campaign) {
                $this->io->progressAdvance();
                $this->updateReport($campaign);
                $this->entityManager->flush();
                usleep(500);
                ++$offset;
            }

            $this->entityManager->clear();

            $paginator->getQuery()->setFirstResult($offset);
        } while (0 !== $offset && $offset < $total);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function createPaginator(?\DateTimeInterface $from): Paginator
    {
        $qb = $this->repository
            ->createQueryBuilder('mc')
            ->addSelect('report')
            ->leftJoin('mc.report', 'report')
            ->where('mc.status = :status')
            ->setParameter('status', MailchimpCampaign::STATUS_SENT)
            ->setMaxResults(500)
        ;

        if ($from) {
            $qb
                ->andWhere('mc.updatedAt >= :from')
                ->setParameter('from', $from)
            ;
        }

        return new Paginator($qb);
    }

    private function updateReport(MailchimpCampaign $campaign): void
    {
        if (empty($data = $this->manager->getReportData($campaign))) {
            return;
        }

        $report = $campaign->getReport() ?? new MailchimpCampaignReport();

        $report->setOpenTotal($this->propertyAccessor->getValue($data, '[opens][opens_total]'));
        $report->setOpenUnique($this->propertyAccessor->getValue($data, '[opens][unique_opens]'));
        $report->setOpenRate(($rate = $this->propertyAccessor->getValue($data, '[opens][open_rate]')) ? round($rate * 100.0, 2) : 0);
        $report->setLastOpen(
            ($date = $this->propertyAccessor->getValue($data, '[opens][last_open]')) ?
                new \DateTime($date) :
                null
        );

        $report->setClickTotal($this->propertyAccessor->getValue($data, '[clicks][clicks_total]'));
        $report->setClickUnique($this->propertyAccessor->getValue($data, '[clicks][unique_clicks]'));
        $report->setClickRate(($rate = $this->propertyAccessor->getValue($data, '[clicks][click_rate]')) ? round($rate * 100.0, 2) : 0);
        $report->setLastClick(
            ($date = $this->propertyAccessor->getValue($data, '[clicks][last_click]')) ?
                new \DateTime($date) :
                null
        );

        $report->setEmailSent($emailSent = $this->propertyAccessor->getValue($data, '[emails_sent]'));
        $report->setUnsubscribed($unsub = $this->propertyAccessor->getValue($data, '[unsubscribed]'));
        $report->setUnsubscribedRate($emailSent > 0 ? round($unsub * 100.0 / $emailSent, 2) : 0);

        $campaign->setReport($report);
    }
}
