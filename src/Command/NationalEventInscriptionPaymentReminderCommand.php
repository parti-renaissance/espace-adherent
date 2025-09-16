<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\SendPaymentReminderCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:national-event:payment-reminder',
    description: 'This command finds pending payments for national events and send a reminder',
)]
class NationalEventInscriptionPaymentReminderCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->eventInscriptionRepository->cancelAllWithWaitingPayments($now = new \DateTime());

        foreach ($this->getInscriptions($now) as $inscription) {
            $this->bus->dispatch(new SendPaymentReminderCommand($inscription->getUuid()));
        }

        return self::SUCCESS;
    }

    /**
     * @return EventInscription[]
     */
    private function getInscriptions(\DateTime $now): array
    {
        return $this->eventInscriptionRepository->findAllWithPendingPayments($now);
    }
}
