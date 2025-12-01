<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Notification\NewMembershipNotificationCommand;
use App\Entity\Adherent;
use App\Entity\CommandHistory;
use App\Entity\CommandHistoryTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\CommandHistoryRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:membership:send-notification',
    description: 'Send adhesion report to RCL',
)]
class SendNewMembershipNotificationCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly CommandHistoryRepository $commandHistoryRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastCommandDate = $this->getLastCommandDate();
        $currentDate = new \DateTimeImmutable();

        $managers = $this->getManagersToNotify();

        $this->io->text(\sprintf('Found %d manager(s) to process about new memberships', $count = \count($managers)));

        $this->io->progressStart($count);

        foreach ($managers as $manager) {
            $this->bus->dispatch(new NewMembershipNotificationCommand($manager->getUuid(), $lastCommandDate, $currentDate));
        }

        $this->io->progressFinish();

        $this->saveCommandHistory($currentDate);

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function getManagersToNotify(): array
    {
        return $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->leftJoin('adherent.animatorCommittees', 'animator_committee')
            ->leftJoin(
                'adherent.zoneBasedRoles',
                'zone_based_role',
                Join::WITH,
                'zone_based_role.adherent = adherent AND zone_based_role.type IN (:zone_based_role_types)'
            )
            ->andWhere('adherent.status = :status')
            ->andWhere(new Orx()
                ->add('animator_committee.id IS NOT NULL')
                ->add('zone_based_role.id IS NOT NULL')
            )
            ->setParameters([
                'status' => Adherent::ENABLED,
                'zone_based_role_types' => [
                    ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                    ScopeEnum::DEPUTY,
                ],
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    private function getLastCommandDate(): \DateTimeInterface
    {
        $lastCommandHistory = $this->commandHistoryRepository->findLastOfType(CommandHistoryTypeEnum::NEW_MEMBERSHIP_NOTIFICATION);

        return $lastCommandHistory ? $lastCommandHistory->createdAt : new \DateTime('1 week ago');
    }

    private function saveCommandHistory(\DateTimeInterface $createdAt): void
    {
        $this->entityManager->persist(new CommandHistory(CommandHistoryTypeEnum::NEW_MEMBERSHIP_NOTIFICATION, $createdAt));
        $this->entityManager->flush();
    }
}
