<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Webhook\EventTypeEnum;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentProfileUpdateHandler extends AbstractAdherentHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $interests;
    private SubscriptionHandler $subscriptionHandler;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        SubscriptionHandler $subscriptionHandler,
        array $adherentInterests,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
    ) {
        $this->subscriptionHandler = $subscriptionHandler;
        $this->interests = $adherentInterests;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $mergeFields = $data['merges'] ?? [];

            $groups = $mergeFields['GROUPINGS'] ?? [];

            $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

            $this->updateSubscriptions($adherent, $this->findGroupById($groups, $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()));
            $this->updateInterests($adherent, $this->findGroupById($groups, $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId()));

            $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UPDATE_PROFILE === $type && parent::support($type, $listId);
    }

    private function updateInterests(Adherent $adherent, array $data): void
    {
        if (!isset($data['groups'])) {
            return;
        }

        if ('' === $data['groups']) {
            $adherent->setInterests([]);

            return;
        }

        $interestLabels = array_map('html_entity_decode', explode(', ', $data['groups']));

        foreach ($interestLabels as $label) {
            if (!\in_array($label, $this->interests, true)) {
                $this->logger->error(\sprintf(
                    '[MailchimpWebhook] Mailchimp interest label "%s" does not match any EM interest labels',
                    $label
                ));

                return;
            }
        }

        $adherent->setInterests(array_keys(array_intersect($this->interests, $interestLabels)));

        $this->entityManager->flush();
    }

    private function updateSubscriptions(Adherent $adherent, array $data): void
    {
        if (!isset($data['groups'])) {
            return;
        }

        $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
            $adherent->getSubscriptionTypeCodes(),
            '' === $data['groups'] ? [] : explode(', ', $data['groups'])
        );

        $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);
    }

    private function findGroupById(array $groups, string $uniqueIdToMatch): array
    {
        foreach ($groups as $group) {
            if (isset($group['unique_id']) && $group['unique_id'] === $uniqueIdToMatch) {
                return $group;
            }
        }

        return [];
    }
}
