<?php

namespace App\Mailchimp;

use App\Subscription\SubscriptionTypeEnum;

final class MailchimpSubscriptionLabelMapping
{
    private static $mapping = [
        'Recevoir les emails du national' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
        'Recevoir la newsletter hebdomadaire nationale' => SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
        'Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription' => SubscriptionTypeEnum::DEPUTY_EMAIL,
        'Recevoir les emails de mon Comité local' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
        'Recevoir les emails de mon Assemblée départementale' => SubscriptionTypeEnum::REFERENT_EMAIL,
        'Recevoir les emails des candidats du parti' => SubscriptionTypeEnum::CANDIDATE_EMAIL,
        'Recevoir les emails de ma/mon sénateur/trice' => SubscriptionTypeEnum::SENATOR_EMAIL,
        'Recevoir les informations sur les actions militantes du mouvement par SMS ou MMS' => SubscriptionTypeEnum::MILITANT_ACTION_SMS,
    ];

    public static function getMapping(): array
    {
        return static::$mapping;
    }
}
