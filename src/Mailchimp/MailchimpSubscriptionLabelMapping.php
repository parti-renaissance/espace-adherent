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
        'Recevoir les e-mails de mon candidat aux municipales 2020' => SubscriptionTypeEnum::MUNICIPAL_EMAIL,
        'Recevoir les informations sur les actions militantes du mouvement par SMS ou MMS' => SubscriptionTypeEnum::MILITANT_ACTION_SMS,
    ];

    public static function getSubscriptionTypeCode(string $label): string
    {
        if (!isset(static::$mapping[$label])) {
            throw new \InvalidArgumentException(sprintf('Invalid Mailchimp subscription label "%s"', $label));
        }

        return static::$mapping[$label];
    }

    public static function getMapping(): array
    {
        return static::$mapping;
    }
}
