<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Membership\MembershipRequestHandler;
use App\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemindUnconfirmedMembershipCommand extends Command
{
    protected static $defaultName = 'app:membership:remind-unconfirmed';

    private $membershipRequestHandler;
    private $adherentRepository;
    private $em;

    public function __construct(
        MembershipRequestHandler $membershipRequestHandler,
        AdherentRepository $adherentRepository,
        ObjectManager $em
    ) {
        parent::__construct();

        $this->membershipRequestHandler = $membershipRequestHandler;
        $this->adherentRepository = $adherentRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command finds unconfirmed adherents and send an email reminder')
            ->addArgument('hours', InputArgument::REQUIRED, 'Number of hours before sending an email reminder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = (new \DateTime())->modify(sprintf('-%d hours', (int) $input->getArgument('hours')));

        while ($adherents = $this->findUnconfirmed($from, 100)) {
            foreach ($adherents as $adherent) {
                if ($this->membershipRequestHandler->sendEmailValidation($adherent, true)) {
                    $adherent->setRemindSent(true);
                }
            }
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * @return Adherent[]
     */
    private function findUnconfirmed(\DateTime $from, int $limit): array
    {
        return $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status AND adherent.activatedAt IS NULL')
            ->andWhere('adherent.registeredAt <= :date')
            ->andWhere('adherent.remindSent = false')
            ->setParameter('status', Adherent::DISABLED)
            ->setParameter('date', $from)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
