<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Adherent;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Mailer\MailerService;
use App\Mailer\Message\AdhesionReportMessage;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:report:adhesion',
    description: 'Send adhesion report to Referents, Senators and Deputies',
)]
class SendAdhesionReportCommand extends Command
{
    private $repository;
    private $mailer;

    /** @var SymfonyStyle */
    private $io;
    private $reports = [];

    public function __construct(AdherentRepository $repository, MailerService $transactionalMailer)
    {
        $this->repository = $repository;
        $this->mailer = $transactionalMailer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in days (default: 7)', 7)
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (($interval = (int) $input->getOption('interval')) < 1) {
            throw new \InvalidArgumentException('Interval should be a positive number');
        }

        $dryRunMode = $input->getOption('dry-run');

        if ($dryRunMode) {
            $this->io->note('Dry-Run Mode is ON.');
        }

        $this->io->progressStart(\count($adherents = $this->getAdherentsToNotify()));

        $qb = $this->createCountQueryBuilder($interval);

        foreach ($adherents as $adherent) {
            if ($adherent->isDeputy()) {
                $this->executeForZones(
                    clone $qb,
                    [$adherent->getDeputyZone()],
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                    $adherent,
                    $dryRunMode
                );
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        if ($dryRunMode && $this->reports) {
            $this->io->table(array_keys($this->reports[0]), $this->reports);
        }

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentsToNotify(): array
    {
        return $this->repository->createQueryBuilder('a')
            ->leftJoin('a.zoneBasedRoles', 'zoneBasedRole')
            ->where('a.status = :status')
            ->andWhere('zoneBasedRole.type = :deputy')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'deputy' => ScopeEnum::DEPUTY,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    private function executeForZones(
        QueryBuilder $qb,
        array $zones,
        string $type,
        Adherent $recipient,
        bool $dryRun = false,
    ): void {
        $this->addZoneCondition($qb, $zones);

        $countNewAdherents = $qb->getQuery()->getSingleScalarResult();

        $countSubscribedNewAdherents = $qb
            ->innerJoin('a.subscriptionTypes', 'subscriptionType')
            ->andWhere('subscriptionType.code = :subscription_code AND a.mailchimpStatus = :mailchimp_status')
            ->setParameter('mailchimp_status', ContactStatusEnum::SUBSCRIBED)
            ->setParameter('subscription_code', $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($dryRun) {
            $this->reports[] = [
                'email' => $recipient->getEmailAddress(),
                'type' => $type,
                'total_new' => $countNewAdherents,
                'total_new_subs' => $countSubscribedNewAdherents,
            ];

            return;
        }

        if ($countNewAdherents && $countSubscribedNewAdherents) {
            $this->mailer->sendMessage(
                AdhesionReportMessage::create($recipient, $countNewAdherents, $countSubscribedNewAdherents)
            );
        }
    }

    private function createCountQueryBuilder(int $interval): QueryBuilder
    {
        $startDate = new \DateTime(\sprintf('-%d days', $interval));
        $endDate = new \DateTime('-1 days');

        return $this->repository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->where('a.status = :status')
            ->andWhere('a.activatedAt >= :start_date AND a.activatedAt <= :end_date')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'start_date' => $startDate->format('Y-m-d 00:00:00'),
                'end_date' => $endDate->format('Y-m-d 23:59:59'),
            ])
        ;
    }

    private function addZoneCondition(QueryBuilder $qb, array $zones, string $alias = 'a'): void
    {
        $this->repository->withGeoZones(
            $zones,
            $qb->innerJoin("$alias.zones", 'zone'),
            $alias,
            Adherent::class,
            'a2',
            'zones',
            'z2'
        );
    }
}
