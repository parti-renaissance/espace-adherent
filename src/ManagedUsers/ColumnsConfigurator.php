<?php

namespace App\ManagedUsers;

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
            ],
            [
                'key' => 'email_subscription',
                'label' => 'Abonné email',
                'type' => self::COLUMN_TYPE_BOOLEAN,
            ],
            [
                'key' => 'sms_subscription',
                'label' => 'Abonné tel',
                'type' => self::COLUMN_TYPE_BOOLEAN,
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
                'key' => 'department_code',
                'label' => 'Code département',
            ],
            [
                'key' => 'department',
                'label' => 'Département',
            ],
            [
                'key' => 'region_code',
                'label' => 'Code région',
            ],
            [
                'key' => 'region',
                'label' => 'Région',
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
