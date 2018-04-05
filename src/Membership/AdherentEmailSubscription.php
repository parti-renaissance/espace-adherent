<?php

namespace AppBundle\Membership;

final class AdherentEmailSubscription
{
//    CANARY !
    public const SUBSCRIBED_EMAILS_MAIN = 'subscribed_emails_main';
    public const SUBSCRIBED_EMAILS_LOCAL_HOST = 'subscribed_emails_local_host';

    public const SUBSCRIBED_EMAILS_MOVEMENT_INFORMATION = 'subscribed_emails_movement_information';
    public const SUBSCRIBED_EMAILS_GOVERNMENT_INFORMATION = 'subscribed_emails_government_information';
    public const SUBSCRIBED_EMAILS_WEEKLY_LETTER = 'subscribed_emails_weekly_letter';
    public const SUBSCRIBED_EMAILS_MOOC = 'subscribed_emails_mooc';
    public const SUBSCRIBED_EMAILS_MICROLEARNING = 'subscribed_emails_microlearning';
    public const SUBSCRIBED_EMAILS_DONATOR_INFORMATION = 'subscribed_emails_donator_information';
    public const SUBSCRIBED_EMAILS_REFERENTS = 'subscribed_emails_referents';
    public const SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION = 'subscribed_emails_citizen_project_creation';
//
//    public const SUBSCRIPTIONS = [
//        'E-mails de communication' => [
//            'Recevoir les informations sur le mouvement' => self::SUBSCRIBED_EMAILS_MOVEMENT_INFORMATION,
//            'Recevoir les informations sur le gouvernement' => self::SUBSCRIBED_EMAILS_GOVERNMENT_INFORMATION,
//            'Recevoir la newsletter hebdomadaire LaREM' => self::SUBSCRIBED_EMAILS_WEEKLY_LETTER,
//        ],
//        'E-mails liés à la formation' => [
//            'Recevoir les informations sur le MOOC' => self::SUBSCRIBED_EMAILS_MOOC,
//            'Recevoir les informations sur le micro-learning' => self::SUBSCRIBED_EMAILS_MICROLEARNING,
//        ],
//        'Donateurs' => [
//            'Recevoir les informations destinées aux donateurs' => self::SUBSCRIBED_EMAILS_DONATOR_INFORMATION,
//        ],
//        'Autres e-mails' => [
//            'Recevoir les e-mails de votre référent départemental' => self::SUBSCRIBED_EMAILS_REFERENTS,
//            'Être notifié(e) de la création de nouveaux projets citoyens' => self::SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION,
//        ],
//    ];

    public const SUBSCRIPTIONS = [
        'Emails En Marche !' => self::SUBSCRIBED_EMAILS_MAIN,
        'Emails de vos référents' => self::SUBSCRIBED_EMAILS_REFERENTS,
        'Emails de votre animateur local' => self::SUBSCRIBED_EMAILS_LOCAL_HOST,
        'Être notifié(e) de la création de nouveaux projets citoyens' => self::SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION,
    ];

    public const DISTANCE_2KM = 2;
    public const DISTANCE_5KM = 5;
    public const DISTANCE_10KM = 10;
    public const DISTANCE_20KM = 20;
    public const DISTANCE_50KM = 50;
    public const DISTANCE_100KM = 100;
    public const DISTANCE_ALL = 0;

    public const CITIZEN_PROJECT_DISTANCE_NOTIFICATION = [
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

    /**
     * @return string[]
     */
    public static function getMergedSubscriptions(): array
    {
//        CANARY !
//        return array_merge_recursive(...array_values(static::SUBSCRIPTIONS));
        return static::SUBSCRIPTIONS;
    }
}
