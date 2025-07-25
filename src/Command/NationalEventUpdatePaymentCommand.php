<?php

namespace App\Command;

use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Payment\Direct;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:national-event:update-payment')]
class NationalEventUpdatePaymentCommand extends Command
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly Direct $direct,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('period', null, InputOption::VALUE_REQUIRED, 'The period to cancel waiting payments (in min, default: 30)', 30);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->refreshPaymentStatus();
        $this->closeOldPayments($input);

        return self::SUCCESS;
    }

    private function refreshPaymentStatus(): void
    {
        foreach ($this->paymentRepository->findBy(['status' => PaymentStatusEnum::PENDING]) as $payment) {
            $response = $this->direct->getStatus($payment->getUuid()->toString());
            sleep(1);

            if (str_contains($response, 'STATUS=""')) {
                continue;
            }

            $xml = simplexml_load_string($response, 'SimpleXMLElement', \LIBXML_NOCDATA);
            if (false === $xml) {
                throw new \RuntimeException('Invalid XML');
            }

            $payload = current(json_decode(json_encode($xml), true));

            $this->messageBus->dispatch(new PaymentStatusUpdateCommand($payload));
        }
    }

    private function closeOldPayments(InputInterface $input): void
    {
        $this->paymentRepository->cancelWaitingPayments(new \DateTime('-'.$input->getOption('period').' minutes'));
    }
}
