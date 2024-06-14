<?php

namespace App\Command;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:procuration:migrate-slots',
    description: 'This command migrate data with new feature or slots per round',
)]
class MigrateProcurationSlotsCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly RequestRepository $requestRepository,
        private readonly ProxyRepository $proxyRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, '', 500)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = $input->getOption('batch-size');

        $this->io->title('Migrating requests');

        $paginator = $this->getRequestQueryBuilder();
        $total = $paginator->count();

        $this->io->text(sprintf('Found %s requests to migrate', $total));

        $paginator->getQuery()->setMaxResults($batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $request) {
                $this->migrateRequestSlots($request);

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        $this->io->title('Migrating proxies');

        $paginator = $this->getProxyQueryBuilder();
        $total = $paginator->count();

        $this->io->text(sprintf('Found %s proxies to migrate', $total));

        $paginator->getQuery()->setMaxResults($batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $proxy) {
                $this->migrateProxySlots($proxy);

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        $this->io->success('Done.');

        return self::SUCCESS;
    }

    /**
     * @return Paginator|Request[]
     */
    private function getRequestQueryBuilder(): Paginator
    {
        return new Paginator(
            $this
                ->requestRepository
                ->createQueryBuilder('r')
                ->getQuery()
        );
    }

    private function migrateRequestSlots(Request $request): void
    {
        foreach ($request->rounds as $round) {
            $request->requestSlots->add(new RequestSlot($round, $request));
        }

        $this->entityManager->flush();
    }

    /**
     * @return Paginator|Proxy[]
     */
    private function getProxyQueryBuilder(): Paginator
    {
        return new Paginator(
            $this
                ->proxyRepository
                ->createQueryBuilder('p')
                ->getQuery()
        );
    }

    private function migrateProxySlots(Proxy $proxy): void
    {
        foreach ($proxy->rounds as $round) {
            for ($i = 1; $i <= $proxy->slots; ++$i) {
                $proxy->proxySlots->add(new ProxySlot($round, $proxy));
            }
        }

        $this->entityManager->flush();
    }
}
