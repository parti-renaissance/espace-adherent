<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\Sender\SenderInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentMessageManager
{
    /** @param SenderInterface[] $senders */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly iterable $senders,
        private readonly AdherentMessageScopeInitializer $scopeInitializer,
    ) {
    }

    public function updateFilter(AdherentMessageInterface $message, ?AdherentMessageFilterInterface $filter): void
    {
        $message->setFilter($filter);

        $this->eventDispatcher->dispatch(new MessageEvent($message), Events::MESSAGE_FILTER_PRE_EDIT);

        $this->em->flush();
    }

    public function duplicate(AdherentMessageInterface $message): AdherentMessageInterface
    {
        $cloneMessage = clone $message;

        $this->scopeInitializer->initializeFromScope($cloneMessage, forceReset: true);

        $this->em->persist($cloneMessage);

        $this->eventDispatcher->dispatch(new MessageEvent($cloneMessage), Events::MESSAGE_PRE_CREATE);

        $this->em->flush();

        return $cloneMessage;
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): void
    {
        $this->dispatchSenders($message, $recipients);

        $message->markAsSent();
        $this->em->flush();
    }

    /**
     * Publication-only send path: dispatches the side-channel senders (push, etc.) and persists
     * the SENT status immediately. The campaign email send is NOT performed here — it is scheduled
     * asynchronously by the audience preparation pipeline (FinalizeCampaignAudienceHandler
     * dispatches TriggerSesCampaignMessage by default, or SendMailchimpCampaignCommand when the
     * PUBLICATION_SEND_VIA_MAILCHIMP fallback flag is on).
     *
     * The flush triggers the Algolia postUpdate listener, which indexes the message into the
     * Timeline synchronously.
     */
    public function sendPublication(AdherentMessage $message): void
    {
        $this->dispatchSenders($message);

        $message->markAsSent();
        $this->em->flush();
    }

    private function dispatchSenders(AdherentMessageInterface $message, array $recipients = []): void
    {
        foreach ($this->senders as $sender) {
            if (!$sender->supports($message, false)) {
                continue;
            }

            $sender->send($message, $recipients);
        }
    }

    public function sendTest(AdherentMessageInterface $message, Adherent $user): bool
    {
        return ($sender = $this->getSender($message, true)) && $sender->sendTest($message, [$user]);
    }

    public function getRecipients(AdherentMessageInterface $message): array
    {
        if (!$message->isStatutory()) {
            return [];
        }

        $filter = $message->getFilter();

        if (!$filter instanceof AdherentMessageFilter) {
            return [];
        }

        return $this->em->getRepository(Adherent::class)->getAllInZones($filter->getZones()->toArray(), true, false);
    }

    private function getSender(AdherentMessageInterface $message, bool $forTest): ?SenderInterface
    {
        foreach ($this->senders as $sender) {
            if ($sender->supports($message, $forTest)) {
                return $sender;
            }
        }

        return null;
    }
}
