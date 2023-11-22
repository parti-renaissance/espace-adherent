<?php

namespace App\Command;

use ApiPlatform\Api\UrlGeneratorInterface;
use App\Adherent\Notification\NotificationTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\AdherentNotification;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceNewAdherentsNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:membership:send-notification',
    description: 'Send adhesion report to RCL',
)]
class SendNewMembershipNotificationCommand extends Command
{

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly ZoneRepository $zoneRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer,
        private readonly string $jemengageHost
    )
    {

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $managers = $this->getManagersToNotify();

        $this->io->progressStart(\count($managers));

        foreach ($managers as $manager) {
            $zones = $this->getZonesToNotify($manager);

            $newSympathizers = $this->getNewSympathizers($zones);
            $newAdherents = $this->getNewAdherents($zones);

            $this->sendNotificationMessage($manager, count($newSympathizers), count($newAdherents));
            $this->createNotificationHistories($newSympathizers, $newAdherents);
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function getManagersToNotify(): array
    {
        return $this->adherentRepository
            ->createQueryBuilder('a')
            ->leftJoin('a.zoneBasedRoles', 'zoneBasedRole')
            ->leftJoin('a.senatorArea', 'senatorArea')
            ->where('a.status = :status')
            ->andWhere('a.adherent = :true')
            ->andWhere(
                (new Orx())
                    ->add('zoneBasedRole.type = :type_pad') // Select Referents
                    ->add('zoneBasedRole.type = :deputy') // Select Deputies
                    ->add('senatorArea.departmentTag IS NOT NULL') // Select Senators
            )
            ->setParameters([
                'status' => Adherent::ENABLED,
                'type_pad' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'deputy' => ScopeEnum::DEPUTY,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    private function getNewSympathizers(array $zones): array
    {
        return $this->getAdherentsWithoutNotificationType($zones, NotificationTypeEnum::NEW_SYMPATHISER, false, true);
    }

    private function getNewAdherents(array $zones): array
    {
        return $this->getAdherentsWithoutNotificationType($zones, NotificationTypeEnum::NEW_MEMBERSHIP, true, false);
    }

    /**
     * @return Adherent[]|array
     */
    private function getAdherentsWithoutNotificationType(
        array $zones,
        NotificationTypeEnum $notificationType,
        bool $adherentRenaissance,
        bool $sympathizerRenaissance
    ): array {

        return $this->adherentRepository
            ->createQueryBuilderForZones($zones, $adherentRenaissance, $sympathizerRenaissance)
            ->leftJoin(
                AdherentNotification::class,
                'notification',
                Join::WITH,
                'notification.adherent = adherent AND notification.type = :notification_type'
            )
            ->andWhere('notification.id IS NULL')
            ->setParameter('notification_type', $notificationType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Zone[]|array
     */
    private function getZonesToNotify(Adherent $manager): array
    {
        $zones = [];

        if ($manager->isSenator()) {
            $zone = $this->zoneRepository->findOneBy([
                'type' => Zone::DEPARTMENT,
                'code' => $manager->getSenatorArea()->getDepartmentTag()
            ]);

            if ($zone) {
                $zones[] = $zone;
            }
        }

        if ($manager->isDeputy()) {
            $zone = $manager->getDeputyZone();

            if ($zone) {
                $zones[] = $zone;
            }
        }

        if ($manager->isPresidentDepartmentalAssembly()) {
            $zones = array_merge($zones, $manager->getPresidentDepartmentalAssemblyZones());
        }

        return $zones;
    }

    private function createNotification(Adherent $adherent, NotificationTypeEnum $type): AdherentNotification
    {
        return new AdherentNotification($adherent, $type);
    }

    private function sendNotificationMessage(Adherent $adherent, int $newsympathizersCount, int $newAdherentsCount): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceNewAdherentsNotificationMessage::create(
            $adherent,
            $newsympathizersCount,
            $newAdherentsCount,
            $this->generateJMEMilitantsUrl()
        ));
    }

    private function generateJMEMilitantsUrl(): string
    {
        return '//'.$this->jemengageHost.'/militants';
    }

    private function createNotificationHistories(array $newSympathizers, array $newAdherents): void
    {
        foreach ($newSympathizers as $adherent) {
            $this->entityManager->persist($this->createNotification($adherent, NotificationTypeEnum::NEW_SYMPATHISER));
        }

        foreach ($newAdherents as $adherent) {
            $this->entityManager->persist($this->createNotification($adherent, NotificationTypeEnum::NEW_MEMBERSHIP));
        }

        $this->entityManager->flush();
    }
}
