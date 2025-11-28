<?php

declare(strict_types=1);

namespace App\Mailer\Handler;

use App\Mailer\Command\SendMessageCommandInterface;
use App\Mailer\EmailClientInterface;
use App\Repository\Email\EmailLogRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendMessageCommandHandler
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

        $delivered = $this->client->sendEmail($email->getRequestPayloadJson(), $command->isResend(), $email->useTemplateEndpoint);

        if ($delivered && false === $command->isResend()) {
            $this->emailRepository->setDelivered($email, $delivered);
        }
    }
}
