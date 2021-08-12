<?php

namespace App\Command;

use App\Donation\DonationRequestUtils;
use App\Entity\Donation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DonationUpdateReferenceCommand extends Command
{
    private const BATCH_SIZE = 1000;

    private $em;
    private $donationRequestUtils;

    public function __construct(EntityManagerInterface $em, DonationRequestUtils $donationRequestUtils)
    {
        $this->em = $em;
        $this->donationRequestUtils = $donationRequestUtils;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:donations:update-reference')
            ->setDescription('Update Donations reference')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Donations reference update.']);

        $this->em->beginTransaction();

        try {
            $this->updateDonationsReference($output);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $output->writeln(['', 'Donations reference updated successfully!']);

        return 0;
    }

    private function updateDonationsReference(OutputInterface $output): void
    {
        $progressBar = new ProgressBar($output, $this->countDonations());

        $count = 0;
        foreach ($this->getDonations() as $result) {
            /** @var Donation $donation */
            $donation = reset($result);

            $reference = $this->donationRequestUtils->buildDonationReference(
                $donation->getUuid(),
                $donation->getDonator()->getFullName(),
            );

            $donation->setPayboxOrderRef($reference);

            $progressBar->advance();

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
            }

            ++$count;
        }

        $progressBar->finish();

        $this->em->flush();
        $this->em->clear();

        $output->writeln(['', "Updated $count Donations reference."]);
    }

    private function getDonations(): IterableResult
    {
        return $this
            ->createDonationQueryBuilder()
            ->andWhere('donation.type = :type')
            ->setParameter('type', Donation::TYPE_CB)
            ->getQuery()
            ->iterate()
        ;
    }

    private function countDonations(): int
    {
        return $this
            ->createDonationQueryBuilder()
            ->select('count(donation)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createDonationQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Donation::class)
            ->createQueryBuilder('donation')
        ;
    }
}
