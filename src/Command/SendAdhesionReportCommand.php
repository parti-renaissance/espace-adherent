<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use App\Mailer\MailerService;
use App\Mailer\Message\AdhesionReportMessage;
use App\Repository\AdherentRepository;
use App\Repository\ReferentTagRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendAdhesionReportCommand extends Command
{
    protected static $defaultName = 'app:report:adhesion';

    private $repository;
    private $mailer;

    /** @var SymfonyStyle */
    private $io;
    private $reports = [];

    public function __construct(AdherentRepository $repository, MailerService $mailer)
    {
        $this->repository = $repository;
        $this->mailer = $mailer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send adhesion report to Referents, Senators and Deputies')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in days (default: 7)', 7)
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
            if ($adherent->isReferent()) {
                $this->executeForTags(
                    clone $qb,
                    $adherent->getManagedArea()->getTags()->toArray(),
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    $adherent,
                    $dryRunMode
                );
            }

            if ($adherent->isDeputy()) {
                $this->executeForTags(
                    clone $qb,
                    [$adherent->getManagedDistrict()->getReferentTag()],
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                    $adherent,
                    $dryRunMode
                );
            }

            if ($adherent->isSenator()) {
                $this->executeForTags(
                    clone $qb,
                    [$adherent->getSenatorArea()->getDepartmentTag()],
                    SubscriptionTypeEnum::SENATOR_EMAIL,
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
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentsToNotify(): array
    {
        return $this->repository->createQueryBuilder('a')
            ->where('a.status = :status AND a.adherent = :true')
            ->andWhere(
                (new Orx())
                    ->add('a.managedArea IS NOT NULL') // Select Referents
                    ->add('a.managedDistrict IS NOT NULL') // Select Deputies
                    ->add('a.senatorArea IS NOT NULL') // Select Senators
            )
            ->setParameters([
                'status' => Adherent::ENABLED,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param ReferentTag[] $tags
     */
    private function executeForTags(
        QueryBuilder $qb,
        array $tags,
        string $type,
        Adherent $recipient,
        bool $dryRun = false
    ): void {
        $this->addZoneCondition($qb, $tags);

        $countNewAdherents = $qb->getQuery()->getSingleScalarResult();

        $countSubscribedNewAdherents = $qb
            ->innerJoin('a.subscriptionTypes', 'subscriptionType')
            ->andWhere('subscriptionType.code = :subscription_code AND a.emailUnsubscribed = :false')
            ->setParameter('false', false)
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
        $startDate = new \DateTime(sprintf('-%d days', $interval));
        $endDate = new \DateTime('-1 days');

        return $this->repository->createQueryBuilder('a')
            ->select('COUNT(1)')
            ->leftJoin('a.referentTags', 'tags')
            ->where('a.status = :status AND a.adherent = :true')
            ->andWhere('a.activatedAt >= :start_date AND a.activatedAt <= :end_date')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'true' => true,
                'start_date' => $startDate->format('Y-m-d 00:00:00'),
                'end_date' => $endDate->format('Y-m-d 23:59:59'),
            ])
        ;
    }

    private function addZoneCondition(QueryBuilder $qb, array $tags, string $alias = 'a'): void
    {
        $zoneCondition = new Orx();

        if (array_filter($tags, function (ReferentTag $tag) {
            return ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $tag->getCode();
        })) {
            $zoneCondition->add(sprintf("%s.postAddress.country != 'FR'", $alias));
        }

        $zoneCondition->add('tags IN (:tags)');

        $qb
            ->andWhere($zoneCondition)
            ->setParameter('tags', $tags)
        ;
    }
}
