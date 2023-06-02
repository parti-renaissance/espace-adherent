<?php

namespace App\Command;

use App\Producer\MailerProducerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailerPublishCommand extends Command
{
    public function __construct(private readonly MailerProducerInterface $transactionalMailProducer)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:mailer:publish')
            ->addArgument('uuid', InputArgument::REQUIRED)
            ->setDescription('Publish a message in RabbitMQ for the given UUID to redeliver the e-mail')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException('Invalid UUID');
        }

        $this->transactionalMailProducer->publish(json_encode(['uuid' => $uuid]));

        return 0;
    }
}
