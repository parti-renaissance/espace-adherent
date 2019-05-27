<?php

namespace AppBundle\Mailchimp;

use AppBundle\Subscription\SubscriptionTypeEnum;

final class MailchimpSubscriptionLabelMapping
{
    private static $mapping = [
        'Recevoir les e-mails d\'information de LaREM' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
        'Recevoir la newsletter hebdomadaire de LaREM' => SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
        'Recevoir les e-mails de mon député' => SubscriptionTypeEnum::DEPUTY_EMAIL,
        'Recevoir les e-mails de mon animateur de comité' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
        'Recevoir les e-mails de mon référent territorial' => SubscriptionTypeEnum::REFERENT_EMAIL,
        'Recevoir les e-mails de mon porteur de projet citoyen' => SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL,
    ];

    public static function getSubscriptionTypeCode(string $label): string
    {
        if (!isset(static::$mapping[$label])) {
            throw new \InvalidArgumentException(sprintf('Invalid Mailchimp subscription label "%s"', $label));
        }

        return static::$mapping[$label];
    }
}
