<?php

namespace App\Consumer;

use App\Mailer\MailerService;
use App\Mailer\Message\Message;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailerConsumer extends AbstractConsumer
{
    /**
     * @var MailerService
     */
    private $mailerService;

    public function setMailerService(MailerService $mailerService): void
    {
        $this->mailerService = $mailerService;
    }

    protected function configureDataConstraints(): array
    {
        return [
            'fromName' => [new NotBlank()],
            'fromEmail' => [new NotBlank()],
            'subject' => [new NotBlank()],
            'templateKey' => [new NotBlank()],
            'recipients' => [new Count(['min' => 1])],
        ];
    }

    protected function isExtraFieldsAllowed(): bool
    {
        return true;
    }

    protected function doExecute(array $data): int
    {
        $firstRecipient = array_pop($data['recipients']);

        $message = new Message(
            Uuid::uuid4(),
            $firstRecipient['email'],
            $firstRecipient['name'],
            $data['subject'],
            $data['templateVars'] ?? [],
            $firstRecipient['templateVars'] ?? [],
            null,
            $data['templateKey']
        );

        $message->setSenderEmail($data['fromEmail']);
        $message->setSenderName($data['fromName']);

        $this->mailerService->sendMessage($message);

        return ConsumerInterface::MSG_ACK;
    }
}
