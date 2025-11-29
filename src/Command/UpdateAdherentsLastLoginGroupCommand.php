<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\LastLoginGroupEnum;
use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'adherents:update:last-login-group',
)]
class UpdateAdherentsLastLoginGroupCommand extends Command
{
    private $adherentRepository;
    private $entityManager;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(AdherentRepository $adherentRepository, ObjectManager $entityManager)
    {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getPaginator();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                $newGroup = $this->computeLastLoginGroup($adherent);

                if ($adherent->getLastLoginGroup() !== $newGroup) {
                    $adherent->setLastLoginGroup($newGroup);

                    $this->entityManager->flush();
                }

                $this->io->progressAdvance();
                ++$offset;
                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getPaginator(): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
        ;

        return new Paginator($queryBuilder->getQuery());
    }

    private function computeLastLoginGroup(Adherent $adherent): ?string
    {
        $lastLoggedAt = $adherent->getLastLoggedAt();

        if (!$lastLoggedAt) {
            return null;
        }

        if ($lastLoggedAt > new \DateTime('1 month ago')) {
            return LastLoginGroupEnum::LESS_THAN_1_MONTH;
        }

        if ($lastLoggedAt > new \DateTime('3 months ago')) {
            return LastLoginGroupEnum::LESS_THAN_3_MONTHS;
        }

        if ($lastLoggedAt > new \DateTime('1 year ago')) {
            return LastLoginGroupEnum::LESS_THAN_1_YEAR;
        }

        return LastLoginGroupEnum::MORE_THAN_1_YEAR;
    }
}
