<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr\Orx;
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
    private $repository;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(AdherentRepository $repository, MailerService $transactionalMailer)
    {
        $this->repository = $repository;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->progressStart(\count($adherents = $this->getAdherentsToNotify()));

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentsToNotify(): array
    {
        return $this->repository->createQueryBuilder('a')
            ->leftJoin('a.zoneBasedRoles', 'zoneBasedRole')
            ->where('a.status = :status AND a.adherent = :true')
            ->andWhere(
                (new Orx())
                    ->add('a.managedArea IS NOT NULL') // Select Referents
                    ->add('zoneBasedRole.type = :deputy') // Select Deputies
                    ->add('a.senatorArea IS NOT NULL') // Select Senators
            )
            ->setParameters([
                'status' => Adherent::ENABLED,
                'deputy' => ScopeEnum::DEPUTY,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
