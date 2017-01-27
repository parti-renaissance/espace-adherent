<?php

namespace AppBundle\Membership;

final class AdherentEmailSubscription
{
    const SUBSCRIBED_EMAILS_MAIN = 'subscribed_emails_main';
    const SUBSCRIBED_EMAILS_REFERENTS = 'subscribed_emails_referents';
    const SUBSCRIBED_EMAILS_LOCAL_HOST = 'subscribed_emails_local_host';

    const SUBSCRIPTIONS = [
        'Emails En Marche !' => self::SUBSCRIBED_EMAILS_MAIN,
        'Emails de vos référents' => self::SUBSCRIBED_EMAILS_REFERENTS,
        'Emails de vôtre animateur local' => self::SUBSCRIBED_EMAILS_LOCAL_HOST,
    ];

    private function __construct()
    {
    }
}
