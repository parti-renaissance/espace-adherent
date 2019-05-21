<?php

namespace AppBundle\Command;

use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\MailchimpCampaignReport;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\MailchimpCampaignRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class MailchimpReportDownloadCommand extends Command
{
    protected static $defaultName = 'mailchimp:report:download';

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
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $mailchimpCampaignRepository;
        $this->manager = $manager;
        $this->propertyAccessor = $propertyAccessor;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Download Mailchimp campaigns reports');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paginator = $this->createPaginator();

        $this->io->progressStart($total = $paginator->count());
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $campaign) {
                $this->io->progressAdvance();
                $this->updateReport($campaign);
                ++$offset;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $paginator->getQuery()->setFirstResult($offset);
        } while ($offset < $total);

        $this->io->progressFinish();
    }

    private function createPaginator(): Paginator
    {
        return new Paginator(
            $this->repository
                ->createQueryBuilder('mc')
                ->addSelect('report')
                ->leftJoin('mc.report', 'report')
                ->where('mc.status = :status')
                ->setParameter('status', MailchimpCampaign::STATUS_SENT)
                ->setMaxResults(1000)
        );
    }

    private function updateReport(MailchimpCampaign $campaign): void
    {
        if (empty($data = $this->manager->getReportData($campaign))) {
            return;
        }

        $report = $campaign->getReport() ?? new MailchimpCampaignReport();

        $report->setOpenTotal($this->propertyAccessor->getValue($data, '[opens][opens_total]'));
        $report->setOpenUnique($this->propertyAccessor->getValue($data, '[opens][unique_opens]'));
        $report->setOpenRate($this->propertyAccessor->getValue($data, '[opens][open_rate]'));
        $report->setLastOpen(
            ($date = $this->propertyAccessor->getValue($data, '[opens][last_open]')) ?
                new \DateTime($date) :
                null
        );

        $report->setClickTotal($this->propertyAccessor->getValue($data, '[clicks][clicks_total]'));
        $report->setClickUnique($this->propertyAccessor->getValue($data, '[clicks][unique_clicks]'));
        $report->setClickRate($this->propertyAccessor->getValue($data, '[clicks][click_rate]'));
        $report->setLastClick(
            ($date = $this->propertyAccessor->getValue($data, '[clicks][last_click]')) ?
                new \DateTime($date) :
                null
        );

        $report->setEmailSent($this->propertyAccessor->getValue($data, '[emails_sent]'));
        $report->setUnsubscribed($this->propertyAccessor->getValue($data, '[unsubscribed]'));
        $report->setUnsubscribedRate($this->propertyAccessor->getValue($data, '[list_stats][unsub_rate]'));

        $campaign->setReport($report);
    }
}
