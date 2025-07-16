<?php

namespace App\Command;

use App\Repository\NationalEvent\PaymentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:national-event:payment-expire')]
class NationalEventExpirePaymentCommand extends Command
{
    public function __construct(private readonly PaymentRepository $paymentRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('period', null, InputOption::VALUE_REQUIRED, 'The period to cancel waiting payments (in min, default: 30)', 30);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->paymentRepository->cancelWaitingPayments(new \DateTime('-'.$input->getOption('period').' minutes'));

        return self::SUCCESS;
    }
}
