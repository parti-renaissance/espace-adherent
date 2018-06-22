<?php

namespace AppBundle\Consumer;

use AppBundle\Mailer\Exception\MessageNotFoundException;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRegistry;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailerConsumerV2 extends AbstractConsumer
{
    private $mailerService;
    private $registry;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $manager, MessageRegistry $registry, MailerService $mailerService)
    {
        parent::__construct($validator, $manager);

        $this->registry = $registry;
        $this->mailerService = $mailerService;
    }

    protected function configureDataConstraints(): array
    {
        return [
            'app' => [new NotBlank()],
            'fromName' => [new NotBlank()],
            'fromEmail' => [new NotBlank()],
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
        $subject = $data['subject'] ?? null;

        try {
            $messageClass = $this->registry->getMessageClass($data['templateKey']);
        } catch (MessageNotFoundException $e) {
            $this->getLogger()->emergency($e->getMessage(), ['exception' => $e, 'data' => $data]);

            return ConsumerInterface::MSG_REJECT;
        }

        /** @var Message $message */
        $message = new $messageClass(
            Uuid::uuid4(),
            $firstRecipient['email'],
            $firstRecipient['name'],
            $data['templateVars'] ?? [],
            $firstRecipient['templateVars'] ?? [],
            $data['replyTo'] ?? null
        );

        if ($subject) {
            $message->setSubject($subject);
        }

        $message->setApp($data['app']);
        $message->setSenderEmail($data['fromEmail']);
        $message->setSenderName($data['fromName']);

        $this->mailerService->sendMessage($message);

        return ConsumerInterface::MSG_ACK;
    }
}
