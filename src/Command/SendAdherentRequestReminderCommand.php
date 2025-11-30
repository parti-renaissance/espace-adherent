<?php

declare(strict_types=1);

namespace App\Command;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Adhesion\Command\SendAdherentRequestReminderCommand as SendReminderCommand;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:adherent-request:send-reminder',
    description: 'This command finds upcoming events and send email reminders',
)]
class SendAdherentRequestReminderCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly AdherentRequestRepository $adherentRequestRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                \sprintf('Reminder type (one of %s)', implode(', ', [
                    AdherentRequestReminderTypeEnum::AFTER_ONE_HOUR->value,
                    AdherentRequestReminderTypeEnum::NEXT_SATURDAY->value,
                    AdherentRequestReminderTypeEnum::AFTER_THREE_WEEKS->value,
                ]))
            )
            ->addArgument('beforeMinutes', InputArgument::REQUIRED, 'Created before (in minutes ago)')
            ->addArgument('afterMinutes', InputArgument::OPTIONAL, 'Created after (in minutes ago)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $typeValue = $input->getArgument('type');
        $allowedTypes = array_map(fn ($case) => $case->value, AdherentRequestReminderTypeEnum::cases());

        if (!\in_array($typeValue, $allowedTypes, true)) {
            $this->io->error(\sprintf('"%s" is not a valid reminder type. Allowed values: %s', $typeValue, implode(', ', $allowedTypes)));

            return self::FAILURE;
        }

        $type = AdherentRequestReminderTypeEnum::from($typeValue);

        $createdBefore = new \DateTime()->modify(\sprintf('-%d minutes', (int) $input->getArgument('beforeMinutes')));
        $createdAfter = null !== $input->getArgument('afterMinutes')
            ? new \DateTime()->modify(\sprintf('-%d minutes', (int) $input->getArgument('afterMinutes')))
            : null;

        $adherentRequests = $this->adherentRequestRepository->findToRemind($type, $createdBefore, $createdAfter);

        $this->io->progressStart($total = \count($adherentRequests));

        foreach ($adherentRequests as $adherentRequest) {
            $this->bus->dispatch(new SendReminderCommand($adherentRequest->getUuid(), $type));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success("$total adherent requests have been reminded.");

        return self::SUCCESS;
    }
}
