<?php

namespace App\Command;

use App\Adherent\Notification\NewMembershipNotificationHandler;
use App\Entity\Adherent;
use App\Repository\AdherentRepository;
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
        private readonly NewMembershipNotificationHandler $newMembershipNotificationHandler
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
            $this->newMembershipNotificationHandler->handle($manager);
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
            ->innerJoin('a.animatorCommittees', 'ac')
            ->andWhere('a.status = :status')
            ->andWhere('a.adherent = :true')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
