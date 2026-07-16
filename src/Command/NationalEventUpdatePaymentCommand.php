<?php

declare(strict_types=1);

namespace App\Command;

use App\NationalEvent\Payment\Worldline\CheckoutOutcomeResolver;
use App\NationalEvent\Payment\Worldline\HostedCheckoutClientInterface;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:national-event:update-payment')]
class NationalEventUpdatePaymentCommand extends Command
{
    /**
     * Expiring a payment that Worldline can still capture would cancel an inscription about to be paid, so the window
     * outlives the checkout session instead of using the legacy 30 minutes.
     */
    private const EXPIRY_MARGIN_MINUTES = 60;

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly CheckoutOutcomeResolver $checkoutOutcomeResolver,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'period',
            null,
            InputOption::VALUE_REQUIRED,
            'The period to cancel waiting payments (in min)',
            HostedCheckoutClientInterface::SESSION_TIMEOUT_MINUTES + self::EXPIRY_MARGIN_MINUTES
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->refreshPaymentStatus();
        $this->closeOldPayments($input);

        return self::SUCCESS;
    }

    private function refreshPaymentStatus(): void
    {
        foreach ($this->paymentRepository->findToCheck() as $payment) {
            if ($payment->isExpired()) {
                $payment->expiredCheckedAt = new \DateTime();
                $this->entityManager->flush();
            }

            $this->checkoutOutcomeResolver->resolve($payment);
        }
    }

    private function closeOldPayments(InputInterface $input): void
    {
        $this->paymentRepository->cancelWaitingPayments(new \DateTime('-'.$input->getOption('period').' minutes'));
    }
}
