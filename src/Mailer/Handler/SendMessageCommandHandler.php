<?php

namespace App\Mailer\Handler;

use App\Mailer\Command\SendMessageCommandInterface;
use App\Mailer\EmailClientInterface;
use App\Repository\Email\EmailLogRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EmailLogRepository $emailRepository,
        private readonly EmailClientInterface $client,
    ) {
    }

    public function __invoke(SendMessageCommandInterface $command): void
    {
        if (!$email = $this->emailRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if (
            ($delivered = $this->client->sendEmail($email->getRequestPayloadJson(), $command->resend, $email->useTemplateEndpoint))
            && false === $command->resend
        ) {
            $this->emailRepository->setDelivered($email, $delivered);
        }
    }
}
