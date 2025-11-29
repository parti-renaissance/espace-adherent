<?php

declare(strict_types=1);

namespace App\Command;

use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Payment\Direct;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $entityManager,
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
        foreach ($this->paymentRepository->findToCheck() as $payment) {
            $response = $this->direct->getStatus($payment->getUuid()->toString());
            sleep(1);

            if ($payment->isExpired()) {
                $payment->expiredCheckedAt = new \DateTime();
                $this->entityManager->flush();
            }

            if (str_contains($response, 'STATUS=""')) {
                continue;
            }

            $xml = simplexml_load_string($response, 'SimpleXMLElement', \LIBXML_NOCDATA);
            if (false === $xml) {
                throw new \RuntimeException('Invalid XML');
            }

            if (false === $content = json_encode($xml)) {
                throw new \RuntimeException('Invalid XML/JSON conversion : '.$xml);
            }

            $payload = current(json_decode($content, true, 512, \JSON_THROW_ON_ERROR));

            $this->messageBus->dispatch(new PaymentStatusUpdateCommand($payload));
        }
    }

    private function closeOldPayments(InputInterface $input): void
    {
        $this->paymentRepository->cancelWaitingPayments(new \DateTime('-'.$input->getOption('period').' minutes'));
    }
}
