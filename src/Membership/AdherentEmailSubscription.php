<?php

namespace AppBundle\Membership;

final class AdherentEmailSubscription
{
    public const SUBSCRIBED_EMAILS_MOVEMENT_INFORMATION = 'subscribed_emails_movement_information';
    public const SUBSCRIBED_EMAILS_GOVERNMENT_INFORMATION = 'subscribed_emails_government_information';
    public const SUBSCRIBED_EMAILS_WEEKLY_LETTER = 'subscribed_emails_weekly_letter';
    public const SUBSCRIBED_EMAILS_MOOC = 'subscribed_emails_mooc';
    public const SUBSCRIBED_EMAILS_MICROLEARNING = 'subscribed_emails_microlearning';
    public const SUBSCRIBED_EMAILS_DONATOR_INFORMATION = 'subscribed_emails_donator_information';
    public const SUBSCRIBED_EMAILS_REFERENTS = 'subscribed_emails_referents';
    public const SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION = 'subscribed_emails_citizen_project_creation';

    public const SUBSCRIPTIONS = [
        'Mails de communication' => [
            'Informations du mouvement' => self::SUBSCRIBED_EMAILS_MOVEMENT_INFORMATION,
            'Informations du gouvernement' => self::SUBSCRIBED_EMAILS_GOVERNMENT_INFORMATION,
            'Lettre hebdomadaire ' => self::SUBSCRIBED_EMAILS_WEEKLY_LETTER,
        ],
        'Formation' => [
            'Mooc' => self::SUBSCRIBED_EMAILS_MOOC,
            'Microlearning' => self::SUBSCRIBED_EMAILS_MICROLEARNING,
        ],
        'Donateurs' => [
            'Informations donateur' => self::SUBSCRIBED_EMAILS_DONATOR_INFORMATION,
        ],
        'Mails locaux' => [
            'Emails de vos référents' => self::SUBSCRIBED_EMAILS_REFERENTS,
            'Être notifié(e) de la création de nouveaux projets citoyens' => self::SUBSCRIBED_EMAILS_CITIZEN_PROJECT_CREATION,
        ],
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
        return array_merge_recursive(...array_values(static::SUBSCRIPTIONS));
    }
}
