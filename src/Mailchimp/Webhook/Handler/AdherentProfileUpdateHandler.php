<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use AppBundle\Subscription\SubscriptionHandler;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentProfileUpdateHandler extends AbstractAdherentHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $interests;
    private $manager;
    private $subscriptionHandler;
    private $dispatcher;

    public function __construct(
        ObjectManager $manager,
        SubscriptionHandler $subscriptionHandler,
        array $interests,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->manager = $manager;
        $this->subscriptionHandler = $subscriptionHandler;
        $this->interests = $interests;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $mergeFields = $data['merges'] ?? [];

            $groups = $mergeFields['GROUPINGS'] ?? [];

            $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

            $oldSubscriptionTypes = $adherent->getSubscriptionTypes();

            $this->updateSubscriptions($adherent, $this->findGroupById($groups, $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()));
            $this->updateInterests($adherent, $this->findGroupById($groups, $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId()));

            $this->dispatcher->dispatch(UserEvents::USER_UPDATE_SUBSCRIPTIONS, new UserEvent($adherent, null, null, $oldSubscriptionTypes));
        }
    }

    public function support(string $type, string $listId): bool
    {
        return
            \in_array($type, [EventTypeEnum::UPDATE_PROFILE, EventTypeEnum::SUBSCRIBE], true)
            && parent::support($type, $listId)
        ;
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
                $this->logger->error(sprintf(
                    '[MailchimpWebhook] Mailchimp interest label "%s" does not match any EM interest labels',
                    $label
                ));

                return;
            }
        }

        $adherent->setInterests(array_keys(array_intersect($this->interests, $interestLabels)));

        $this->manager->flush();
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
