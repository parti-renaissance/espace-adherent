<?php

declare(strict_types=1);

namespace App\Command;

use App\Mailer\Command\AsyncSendMessageCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:mailer:publish',
    description: 'Publish a message in RabbitMQ for the given UUID to redeliver the email'
)]
class MailerPublishCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('uuid', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException('Invalid UUID');
        }

        $this->bus->dispatch(new AsyncSendMessageCommand(Uuid::fromString($uuid)));

        return self::SUCCESS;
    }
}
