<?php

namespace App\Command;

use App\Donation\PayboxPaymentUnsubscription;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DonationCancelCommand extends Command
{
    protected static $defaultName = 'app:donation:cancel';

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
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription
    ) {
        parent::__construct();

        $this->em = $em;
        $this->donationRepository = $donationRepository;
        $this->payboxPaymentUnsubscription = $payboxPaymentUnsubscription;
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->setDescription('Cancel all monthly donations for a given email.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $this->io->title(sprintf('Cancelling subscribed donations for email "%s"', $email));

        $donations = $this->donationRepository->findAllSubscribedDonationByEmail($email);

        if (!$donations) {
            $this->io->error('No recurring donation found for this email.');

            return;
        }

        foreach ($donations as $donation) {
            try {
                if (!$this->io->confirm(sprintf(
                    'Are you sure you want to cancel the recurring donation id(%d) from email "%s"?',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress()
                ))) {
                    continue;
                }

                $this->payboxPaymentUnsubscription->unsubscribe($donation);
                $this->em->flush();

                $this->io->success(sprintf(
                    'The recurring donation id(%d) from email "%s" has been canceled successfully.',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress()
                ));
            } catch (PayboxPaymentUnsubscriptionException $e) {
                $this->io->error(sprintf(
                    'Subscription donation id(%d) from user email %s have an error: %s',
                    $donation->getId(),
                    $donation->getDonator()->getEmailAddress(),
                    $e->getMessage()
                ));
            }
        }
    }
}
