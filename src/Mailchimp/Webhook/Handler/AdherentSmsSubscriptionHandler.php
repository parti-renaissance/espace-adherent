<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use App\Mailchimp\Webhook\EventTypeEnum;
use App\Repository\SmsOptOutRepository;
use App\Subscription\SubscriptionHandler;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;

class AdherentSmsSubscriptionHandler extends AbstractAdherentHandler
{
    public function __construct(
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly SmsOptOutRepository $smsOptOutRepository,
    ) {
    }

    public function handle(array $data): void
    {
        $email = $data['merges']['EMAIL'] ?? null;

        if (!$email) {
            return;
        }

        if (!$adherent = $this->getAdherent($email)) {
            return;
        }

        $subscriptionCodes = $adherent->getSubscriptionTypeCodes();
        $isSubscribed = ($data['subscription_status'] ?? null) === 'subscribed';

        if ($isSubscribed && !\in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $subscriptionCodes, true)) {
            $subscriptionCodes[] = SubscriptionTypeEnum::MILITANT_ACTION_SMS;
        } elseif (!$isSubscribed) {
            $subscriptionCodes = array_values(array_filter(
                $subscriptionCodes,
                fn (string $code) => SubscriptionTypeEnum::MILITANT_ACTION_SMS !== $code
            ));
        }

        $this->subscriptionHandler->handleUpdateSubscription($adherent, $subscriptionCodes);

        if ($phone = PhoneNumberUtils::format($adherent->getPhone())) {
            if (!$isSubscribed) {
                $this->smsOptOutRepository->add($phone, SmsOptOutSourceEnum::Mailchimp);
            } else {
                $this->smsOptOutRepository->cancelLastActiveOptOut($phone);
            }
        }
    }

    public function support(string $type, string $listId): bool
    {
        return \in_array($type, [EventTypeEnum::SMS_SUBSCRIBE, EventTypeEnum::SMS_UNSUBSCRIBE], true)
            && parent::support($type, $listId);
    }
}
