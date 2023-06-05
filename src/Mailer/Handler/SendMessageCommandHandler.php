<?php

namespace App\Mailer\Handler;

use App\Mailer\Command\SendMessageCommand;
use App\Mailer\EmailClientInterface;
use App\Repository\EmailRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EmailRepository $emailRepository,
        private readonly EmailClientInterface $client
    ) {
    }

    public function __invoke(SendMessageCommand $command): void
    {
        if (!$email = $this->emailRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if ($delivered = $this->client->sendEmail($email->getRequestPayloadJson())) {
            $this->emailRepository->setDelivered($email, $delivered);
        }
    }
}
