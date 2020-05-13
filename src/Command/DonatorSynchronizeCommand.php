<?php

namespace App\Command;

use App\Donation\DonatorManager;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Repository\DonatorRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DonatorSynchronizeCommand extends Command
{
    private const BATCH_SIZE = 200;

    private $manager;
    private $donatorRepository;
    private $donatorManager;
    private $counter = 0;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        ObjectManager $manager,
        DonatorRepository $donatorRepository,
        DonatorManager $donatorManager
    ) {
        $this->manager = $manager;
        $this->donatorRepository = $donatorRepository;
        $this->donatorManager = $donatorManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:donator:synchronize')
            ->setDescription('Synchronize donators with the donations.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Starting synchronization.');
        $this->io->progressStart($this->getCount());

        foreach ($this->getIterableDonations() as $chunk) {
            /** @var Donation $donation */
            $donation = $chunk[0];

            $donator = $this->donatorRepository->findOneForMatching(
                $donation->getEmailAddress(),
                $donation->getFirstName(),
                $donation->getLastName()
            );

            if (!$donator) {
                $donator = $this->createDonatorByDonation($donation);

                $this->manager->persist($donator);
            }

            $donator->addDonation($donation);

            $this->manager->flush();

            $this->io->progressAdvance();

            ++$this->counter;

            if (0 === ($this->counter % self::BATCH_SIZE)) {
                $this->manager->clear();
            }
        }

        $this->manager->flush();
        $this->manager->clear();

        $this->io->progressFinish();
        $this->io->text('Donators successfully synchronized.');
    }

    private function getCount(): int
    {
        return $this
            ->getQueryBuilder()
            ->select('COUNT(donation)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getIterableDonations(): IterableResult
    {
        return $this
            ->getQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this
            ->manager
            ->getRepository(Donation::class)
            ->createQueryBuilder('donation')
        ;
    }

    private function createDonatorByDonation(Donation $donation): Donator
    {
        $donator = new Donator(
            $donation->getLastName(),
            $donation->getFirstName(),
            $donation->getCityName(),
            $donation->getCountry(),
            $donation->getEmailAddress()
        );

        $donator->setIdentifier($this->donatorManager->incrementeIdentifier());

        return $donator;
    }
}
