<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\Poll\LocalPollRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:polls:unpublish-passed-local',
    description: 'Unpublish local polls after the finish date has been passed.',
)]
class UnpublishLocalPollCommand extends Command
{
    private $localPollRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(LocalPollRepository $localPollRepository)
    {
        $this->localPollRepository = $localPollRepository;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = $this->getPassedLocalPollsCount();
        if (0 === $total) {
            $this->io->success('No polls to unpublish.');

            return self::SUCCESS;
        }

        $this->io->title('Starting unpublishing local polls.');

        $this
            ->createPassedLocalPollQueryBuilder()
            ->update()
            ->set('poll.published', ':false')
            ->setParameter('false', false)
            ->getQuery()
            ->execute()
        ;

        $this->io->success(\sprintf(
            '%s local poll%s %s been unpublished successfully.',
            $total,
            $total > 1 ? 's' : '',
            $total > 1 ? 'have' : 'has'
        ));

        return self::SUCCESS;
    }

    private function getPassedLocalPollsCount(): int
    {
        return $this
            ->createPassedLocalPollQueryBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createPassedLocalPollQueryBuilder(): QueryBuilder
    {
        return $this
            ->localPollRepository
            ->createQueryBuilder('poll')
            ->where('poll.published = :true AND poll.finishAt < :now')
            ->setParameters([
                'true' => true,
                'now' => new \DateTime('now'),
            ])
        ;
    }
}
