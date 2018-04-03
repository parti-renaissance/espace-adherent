<?php

namespace AppBundle\Membership;

final class AdherentEmailSubscription
{
    const SUBSCRIBED_EMAILS_MAIN = 'subscribed_emails_main';
    const SUBSCRIBED_EMAILS_REFERENTS = 'subscribed_emails_referents';
    const SUBSCRIBED_EMAILS_LOCAL_HOST = 'subscribed_emails_local_host';
    const SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION = 'subscribed_emails_cp_creation';

    const SUBSCRIPTIONS = [
        'Emails En Marche !' => self::SUBSCRIBED_EMAILS_MAIN,
        'Emails de vos référents' => self::SUBSCRIBED_EMAILS_REFERENTS,
        'Emails de votre animateur local' => self::SUBSCRIBED_EMAILS_LOCAL_HOST,
        'Être notifié(e) de la création de nouveaux projets citoyens' => self::SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION,
    ];

    const DISTANCE_2KM = 2;
    const DISTANCE_5KM = 5;
    const DISTANCE_10KM = 10;
    const DISTANCE_20KM = 20;
    const DISTANCE_50KM = 50;
    const DISTANCE_100KM = 100;
    const DISTANCE_ALL = 0;

    const CITIZEN_PROJECT_DISTANCE_NOTIFICATION = [
        '2Km' => self::DISTANCE_2KM,
        '5Km' => self::DISTANCE_5KM,
        '10Km' => self::DISTANCE_10KM,
        '20Km' => self::DISTANCE_20KM,
        '50Km' => self::DISTANCE_50KM,
        '100Km' => self::DISTANCE_100KM,
        'Toutes' => self::DISTANCE_ALL,
    ];

    private function __construct()
    {
    }
}
