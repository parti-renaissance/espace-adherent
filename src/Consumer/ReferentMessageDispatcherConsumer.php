<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\ReferentManagedUsersMessage;
use AppBundle\Mail\Campaign\ReferentMail;
use AppBundle\Mailer\MailerService;
use AppBundle\Referent\ReferentMessage;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Repository\ReferentManagedUsersMessageRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentMessageDispatcherConsumer extends AbstractConsumer
{
    protected const BATCH_SIZE = 50;

    /**
     * @var MailPostInterface
     */
    private $mailPost;
    /**
     * @var ReferentManagedUsersMessageRepository
     */
    private $referentMessageRepository;
    /**
     * @var ReferentManagedUserRepository
     */
    private $referentManagedUserRepository;

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
        ];
    }

    public function doExecute(array $data): int
    {
        try {
            if (!$referentMessage = $this->getReferentMessageRepository()->findOneByUuid($data['uuid'])) {
                $this->getLogger()->error('Referent message not found', $data);
                $this->writeln($data['uuid'], 'Referent message not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

            $referent = $referentMessage->getFrom();
            $message = ReferentMessage::createFromMessage($referentMessage);
            $this->writeln($data['uuid'], 'Dispatching message from '.$referent->getEmailAddress());

            /** @var IterableResult $results */
            $results = $this->getReferentManagedUserRepository()->createDispatcherIterator($referent, $message->getFilter());

            $i = 0;
            $count = 0;
            $chunk = [];

            foreach ($results as $result) {
                ++$i;
                ++$count;
                $chunk[] = $result[0];

                if (MailerService::PAYLOAD_MAXSIZE === $i) {
                    $this->sendMessage($referentMessage, $message, $chunk, $count);

                    $i = 0;
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                $this->sendMessage($referentMessage, $message, $chunk, $count);
            }

            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    public function sendMessage(
        ReferentManagedUsersMessage $savedMessage,
        ReferentMessage $message,
        array $recipients,
        int $count
    ): void {
        $this->getMailPost()->address(
            ReferentMail::class,
            ReferentMail::createRecipients($recipients),
            ReferentMail::createRecipientFromAdherent($message->getFrom()),
            ReferentMail::createTemplateVars($message),
            $message->getSubject(),
            ReferentMail::createSender($message->getFrom())
        );

        $this->writeln(
            $savedMessage->getUuid()->toString(),
            'Message from '.$message->getFrom()->getEmailAddress().' dispatched ('.$count.')'
        );

        $this->getReferentMessageRepository()->incrementOffset($savedMessage, \count($recipients));
    }

    public function setMailPost(MailPostInterface $mailPost): void
    {
        $this->mailPost = $mailPost;
    }

    public function getMailPost(): MailPostInterface
    {
        return $this->mailPost;
    }

    public function setReferentManagedUserRepository(ReferentManagedUserRepository $referentManagedUserRepository): void
    {
        $this->referentManagedUserRepository = $referentManagedUserRepository;
    }

    public function getReferentManagedUserRepository(): ReferentManagedUserRepository
    {
        return $this->referentManagedUserRepository;
    }

    public function setReferentMessageRepository(ReferentManagedUsersMessageRepository $referentMessageRepository): void
    {
        $this->referentMessageRepository = $referentMessageRepository;
    }

    public function getReferentMessageRepository(): ReferentManagedUsersMessageRepository
    {
        return $this->referentMessageRepository;
    }
}
