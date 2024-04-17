<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:adherent:bde-remind-activation',
    description: 'This command finds non-activated adherents and send an email reminder',
)]
class BesoinDEuropeRemindActivationCommand extends Command
{
    public function __construct(
        private readonly MembershipNotifier $membershipNotifier,
        private readonly AdherentRepository $adherentRepository,
        private readonly ObjectManager $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while ($adherents = $this->findToRemind(100)) {
            foreach ($adherents as $adherent) {
                $this->membershipNotifier->sendActivationReminder($adherent);

                $adherent->setActivationReminded();

                $this->em->flush();
            }

            $this->em->clear();
        }

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function findToRemind(int $limit): array
    {
        return $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.source = :source_bde')
            ->andWhere('adherent.activatedAt IS NULL')
            ->andWhere('adherent.activationRemindedAt IS NULL')
            ->andWhere('adherent.registeredAt < :current_day')
            ->setParameters([
                'source_bde' => MembershipSourceEnum::BESOIN_D_EUROPE,
                'current_day' => date('Y-m-d'),
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
