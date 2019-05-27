<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\MailchimpSubscriptionLabelMapping;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Subscription\SubscriptionHandler;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ProfileUpdateHandler extends AbstractAdherentHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $interests;
    private $manager;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;
    private $subscriptionHandler;

    public function __construct(
        ObjectManager $manager,
        SubscriptionHandler $subscriptionHandler,
        array $interests,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
        $this->subscriptionHandler = $subscriptionHandler;
        $this->interests = $interests;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
        $this->logger = $logger;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $mergeFields = $data['merges'] ?? [];

            $groups = $mergeFields['GROUPINGS'] ?? [];

            $this->updateSubscriptions($adherent, $this->findGroupById($groups, $this->subscriptionTypeInterestGroupId));
            $this->updateInterests($adherent, $this->findGroupById($groups, $this->memberInterestInterestGroupId));
        }
    }

    public function support(string $type): bool
    {
        return \in_array($type, [EventTypeEnum::UPDATE_PROFILE, EventTypeEnum::SUBSCRIBE], true);
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

        $interestLabels = explode(', ', $data['groups']);

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

        if ('' === $data['groups']) {
            $this->subscriptionHandler->unsubscribeAllMails($adherent);

            return;
        }

        $newSubscriptionCodes = array_map(static function (string $label) {
            return MailchimpSubscriptionLabelMapping::getSubscriptionTypeCode($label);
        }, explode(', ', $data['groups']));

        $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionCodes);
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
