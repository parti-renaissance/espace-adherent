<?php

declare(strict_types=1);

namespace App\Command;

use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:donation:cancel',
    description: 'Cancel all monthly donations for a given email.',
)]
class DonationCancelCommand extends Command
{
    private $em;
    private $donationRepository;
    private $payboxPaymentUnsubscription;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        DonationRepository $donationRepository,
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription,
    ) {
        parent::__construct();

        $this->em = $em;
        $this->donationRepository = $donationRepository;
        $this->payboxPaymentUnsubscription = $payboxPaymentUnsubscription;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $this->io->title(\sprintf('Cancelling subscribed donations for email "%s"', $email));

        $donations = $this->donationRepository->findAllSubscribedDonationByEmail($email);

        if (!$donations) {
            $this->io->error('No recurring donation found for this email.');

            return self::FAILURE;
        }

        foreach ($donations as $donation) {
            try {
                if (!$this->io->confirm(\sprintf(
                    'Are you sure you want to cancel the recurring donation id(%d) from email "%s"?',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress()
                ))) {
                    continue;
                }

                $this->payboxPaymentUnsubscription->unsubscribe($donation);
                $this->em->flush();

                $this->io->success(\sprintf(
                    'The recurring donation id(%d) from email "%s" has been canceled successfully.',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress()
                ));
            } catch (PayboxPaymentUnsubscriptionException $e) {
                $this->io->error(\sprintf(
                    'Subscription donation id(%d) from user email %s have an error: %s',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress(),
                    $e->getMessage()
                ));
            }
        }

        return self::SUCCESS;
    }
}
