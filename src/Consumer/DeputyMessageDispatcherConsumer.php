<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\DeputyManagedUsersMessage;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DeputyMessage as Message;
use AppBundle\Deputy\DeputyMessage;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\DeputyManagedUsersMessageRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DeputyMessageDispatcherConsumer extends AbstractConsumer
{
    protected const BATCH_SIZE = 50;

    /**
     * @var MailerService
     */
    private $mailer;
    /**
     * @var DeputyManagedUsersMessageRepository
     */
    private $deputyManagedUsersMessageRepository;
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
        ];
    }

    public function doExecute(array $data): int
    {
        try {
            if (!$deputyMessage = $this->getDeputyManagedUsersMessageRepository()->findOneByUuid($data['uuid'])) {
                $this->getLogger()->error('Deputy message not found', $data);
                $this->writeln($data['uuid'], 'Deputy message not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

            $deputy = $deputyMessage->getFrom();
            $message = DeputyMessage::createFromMessage($deputyMessage);
            $this->writeln($data['uuid'], 'Dispatching message from '.$deputy->getEmailAddress());

            /** @var IterableResult $results */
            $results = $this->getAdherentRepository()->createDispatcherIteratorForDistrict($deputy->getManagedDistrict(), $message->getOffset());

            $i = 0;
            $count = 0;
            $chunk = [$this->getDefaultRecipient()];

            foreach ($results as $result) {
                ++$i;
                ++$count;
                $chunk[] = $result[0];

                if (MailerService::PAYLOAD_MAXSIZE === $i) {
                    $this->sendMessage($deputyMessage, $message, $chunk, $count);

                    $i = 0;
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                $this->sendMessage($deputyMessage, $message, $chunk, $count);
            }

            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    public function sendMessage(
        DeputyManagedUsersMessage $savedMessage,
        DeputyMessage $message,
        array $recipients,
        int $count
    ) {
        $delivered = $this->getMailer()->sendMessage(Message::create($message, $recipients));

        if ($delivered) {
            $this->writeln(
                $savedMessage->getUuid()->toString(),
                'Message from '.$message->getFrom()->getEmailAddress().' dispatched ('.$count.')'
            );

            $this->getDeputyManagedUsersMessageRepository()->incrementOffset($savedMessage, \count($recipients));
        }
    }

    public function setMailer(MailerService $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function getMailer(): MailerService
    {
        return $this->mailer;
    }

    public function setAdherentRepository(AdherentRepository $adherentRepository): void
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function getAdherentRepository(): AdherentRepository
    {
        return $this->adherentRepository;
    }

    public function setDeputyManagedUsersMessageRepository(
        DeputyManagedUsersMessageRepository $deputyManagedUsersMessageRepository
    ): void {
        $this->deputyManagedUsersMessageRepository = $deputyManagedUsersMessageRepository;
    }

    public function getDeputyManagedUsersMessageRepository(): DeputyManagedUsersMessageRepository
    {
        return $this->deputyManagedUsersMessageRepository;
    }

    private function getDefaultRecipient()
    {
        return new class('territoires@en-marche.fr', 'Pôle Territoire') {
            private $email;
            private $name;

            public function __construct(string $email, string $name)
            {
                $this->email = $email;
                $this->name = $name;
            }

            public function getFullName(): string
            {
                return $this->name;
            }

            public function getFirstName(): string
            {
                return $this->name;
            }

            public function getEmailAddress(): string
            {
                return $this->email;
            }
        };
    }
}
