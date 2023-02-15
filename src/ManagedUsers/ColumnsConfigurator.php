<?php

namespace App\ManagedUsers;

use App\Entity\Projection\ManagedUser;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\ValueObject\Genders;

class ColumnsConfigurator
{
    private const COLUMN_TYPE_ARRAY = 'array';
    private const COLUMN_TYPE_BOOLEAN = 'boolean';
    private const COLUMN_TYPE_TRANS = 'trans';

    private array $adherentInterests;

    public function __construct(array $adherentInterests)
    {
        $this->adherentInterests = $adherentInterests;
    }

    public function getConfig(): array
    {
        return [
            [
                'key' => 'gender',
                'label' => 'Genre',
                'type' => self::COLUMN_TYPE_TRANS,
                'messages' => Genders::CHOICES_LABELS,
            ],
            [
                'key' => 'first_name',
                'label' => 'Prénom',
            ],
            [
                'key' => 'last_name',
                'label' => 'Nom',
            ],
            [
                'key' => 'renaissance_membership',
                'label' => 'Renaissance',
                'type' => self::COLUMN_TYPE_TRANS,
                'messages' => [
                    RenaissanceMembershipFilterEnum::ADHERENT_RE => 'Adhérent',
                    RenaissanceMembershipFilterEnum::SYMPATHIZER_RE => 'Sympathisant',
                ],
            ],
            [
                'key' => 'email_subscription',
                'label' => 'Abonné email',
                'type' => self::COLUMN_TYPE_BOOLEAN,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'dependency' => [
                    'fields' => [
                        ['code' => 'email_subscription', 'valid_values' => [true]],
                    ],
                    'mode' => 'color_invalid',
                ],
            ],
            [
                'key' => 'sms_subscription',
                'label' => 'Abonné tel',
                'type' => self::COLUMN_TYPE_BOOLEAN,
            ],
            [
                'key' => 'phone_number',
                'label' => 'Téléphone',
                'type' => self::COLUMN_TYPE_TRANS,
                'messages' => [
                    ManagedUser::NOT_AVAILABLE => 'Non disponible',
                ],
                'dependency' => [
                    'fields' => [
                        ['code' => 'sms_subscription', 'valid_values' => [true]],
                    ],
                    'mode' => 'color_invalid',
                ],
            ],
            [
                'key' => 'postal_code',
                'label' => 'Code postal',
            ],
            [
                'key' => 'city_code',
                'label' => 'Code commune',
            ],
            [
                'key' => 'city',
                'label' => 'Commune',
            ],
            [
                'key' => 'interests',
                'label' => 'Intérêts',
                'type' => self::COLUMN_TYPE_ARRAY.'|'.self::COLUMN_TYPE_TRANS,
                'messages' => $this->adherentInterests,
            ],
        ];
    }
}
