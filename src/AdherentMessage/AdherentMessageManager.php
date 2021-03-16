<?php

namespace App\AdherentMessage;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\Sender\SenderInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentMessageManager
{
    private $em;
    private $eventDispatcher;
    /** @var SenderInterface[] */
    private $senders;

    public function __construct(ObjectManager $em, EventDispatcherInterface $eventDispatcher, iterable $senders)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->senders = $senders;
    }

    public function saveMessage(AdherentMessageInterface $message): void
    {
        if (!$message->getId()) {
            $this->em->persist($message);

            $this->eventDispatcher->dispatch(new MessageEvent($message), Events::MESSAGE_PRE_CREATE);
        }

        $this->em->flush();
    }

    public function updateFilter(AdherentMessageInterface $message, ?AdherentMessageFilterInterface $filter): void
    {
        if ($message->getFilter() !== $filter) {
            $message->setSynchronized(false);
        }

        $message->setFilter($filter);

        $this->eventDispatcher->dispatch(new MessageEvent($message), Events::MESSAGE_FILTER_PRE_EDIT);

        $this->em->flush();
    }

    public function updateMessage(AdherentMessageInterface $message, AdherentMessageDataObject $dataObject): void
    {
        if (
            $message->getContent() !== $dataObject->getContent()
            || $message->getSubject() !== $dataObject->getSubject()
        ) {
            $message->setSynchronized(false);
        }

        $message->updateFromDataObject($dataObject);

        $this->em->flush();
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return ($sender = $this->getSender($message)) ? $sender->send($message, $recipients) : false;
    }

    public function sendTest(AdherentMessageInterface $message, Adherent $user): bool
    {
        return ($sender = $this->getSender($message)) ? $sender->sendTest($message, [$user]) : false;
    }

    public function getMessageContent(AdherentMessageInterface $message): string
    {
        return ($sender = $this->getSender($message)) ? $sender->renderMessage($message, [$message->getAuthor()]) : '';
    }

    private function getSender(AdherentMessageInterface $message): ?SenderInterface
    {
        foreach ($this->senders as $sender) {
            if ($sender->supports($message)) {
                return $sender;
            }
        }

        return null;
    }
}
