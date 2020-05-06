<?php

namespace App\AdherentMessage;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentMessageManager
{
    private $em;
    private $eventDispatcher;

    public function __construct(ObjectManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function saveMessage(AdherentMessageInterface $message): void
    {
        if (!$message->getId()) {
            $this->em->persist($message);

            $this->eventDispatcher->dispatch(Events::MESSAGE_PRE_CREATE, new MessageEvent($message));
        }

        $this->em->flush();
    }

    public function updateFilter(AdherentMessageInterface $message, ?AdherentMessageFilterInterface $filter): void
    {
        if ($message->getFilter() !== $filter) {
            $message->setSynchronized(false);
        }

        $message->setFilter($filter);

        $this->eventDispatcher->dispatch(Events::MESSAGE_FILTER_PRE_EDIT, new MessageEvent($message));

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
}
