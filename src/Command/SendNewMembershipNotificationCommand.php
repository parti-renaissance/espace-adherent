<?php

namespace App\Command;

use App\Adherent\Notification\NewMembershipNotificationCommand;
use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr;
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
        private readonly AdherentRepository $adherentRepository,
        private readonly MessageBusInterface $bus
    ) {
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
            $this->bus->dispatch(new NewMembershipNotificationCommand($manager->getUuid()));
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
            ->createQueryBuilder('adherent')
            ->leftJoin('adherent.animatorCommittees', 'animator_committee')
            ->leftJoin(
                'adherent.zoneBasedRoles',
                'zone_based_role',
                Expr\Join::WITH,
                'zone_based_role.adherent_id = adherent.id AND zone_based_role.type = :type_pad'
            )
            ->andWhere('adherent.status = :status')
            ->andWhere('adherent.adherent = :true')
            ->andWhere((new Orx())
                ->add('animator_committee.id IS NOT NULL')
                ->add('zone_based_role.id IS NOT NULL')
            )
            ->setParameters([
                'status' => Adherent::ENABLED,
                'true' => true,
                'type_pad' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
