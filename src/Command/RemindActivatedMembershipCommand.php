<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:membership:remind-activated',
    description: 'This command finds activated adherents and send an email reminder',
)]
class RemindActivatedMembershipCommand extends Command
{
    private $membershipNotifier;
    private $adherentRepository;
    private $em;

    public function __construct(
        MembershipNotifier $membershipNotifier,
        AdherentRepository $adherentRepository,
        ObjectManager $em
    ) {
        parent::__construct();

        $this->membershipNotifier = $membershipNotifier;
        $this->adherentRepository = $adherentRepository;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('hours', InputArgument::REQUIRED, 'Number of hours before sending an email reminder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = (new \DateTime())->modify(\sprintf('-%d hours', (int) $input->getArgument('hours')));

        while ($adherents = $this->findActivated($from, 100)) {
            foreach ($adherents as $adherent) {
                if ($this->membershipNotifier->sendEmailReminder($adherent)) {
                    $adherent->setMembershipReminded();

                    $this->em->flush();
                }
            }

            $this->em->clear();
        }

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function findActivated(\DateTime $from, int $limit): array
    {
        return $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->andWhere('adherent.adherent = 1')
            ->andWhere('adherent.membershipRemindedAt IS NULL')
            ->andWhere('adherent.activatedAt IS NOT NULL')
            ->andWhere('adherent.activatedAt > :date')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('date', $from)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
