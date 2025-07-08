<?php

namespace App\AdherentMessage;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\Sender\SenderInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentMessageManager
{
    /** @param SenderInterface[] $senders */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly iterable $senders,
    ) {
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

    public function duplicate(AdherentMessageInterface $message): void
    {
        $this->em->persist($cloneMessage = clone $message);

        $this->eventDispatcher->dispatch(new MessageEvent($cloneMessage), Events::MESSAGE_PRE_CREATE);

        $this->em->flush();
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): bool
    {
        if (!$sender = $this->getSender($message)) {
            return false;
        }

        if ($status = $sender->send($message, $recipients)) {
            $message->markAsSent();
            $this->em->flush();
        }

        return $status;
    }

    public function sendTest(AdherentMessageInterface $message, Adherent $user): bool
    {
        return ($sender = $this->getSender($message)) && $sender->sendTest($message, [$user]);
    }

    public function getRecipients(AdherentMessageInterface $message): array
    {
        if (!$message->isStatutory()) {
            return [];
        }

        $filter = $message->getFilter();

        if (!$filter instanceof MessageFilter && !$filter instanceof AudienceFilter) {
            return [];
        }

        return $this->em->getRepository(Adherent::class)->getAllInZones($filter->getZones()->toArray(), true, false);
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
