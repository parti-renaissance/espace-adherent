<?php

namespace App\ManagedUsers;

use App\ValueObject\Genders;

class ColumnsConfigurator
{
    private const COLUMN_TYPE_ARRAY = 'array';
    private const COLUMN_TYPE_BOOLEAN = 'boolean';
    private const COLUMN_TYPE_TRANS = 'trans';

    private const FILTER_TYPE_STRING = 'string';
    private const FILTER_TYPE_SELECT = 'select';
    private const FILTER_TYPE_BOOLEAN = 'boolean';

    private $adherentInterests;

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
                'filter' => [
                    'type' => self::FILTER_TYPE_SELECT,
                    'options' => [
                        'choices' => Genders::CHOICES_LABELS,
                    ],
                ],
            ],
            [
                'key' => 'first_name',
                'label' => 'Prénom',
                'filter' => [
                    'type' => self::FILTER_TYPE_STRING,
                ],
            ],
            [
                'key' => 'last_name',
                'label' => 'Nom',
                'filter' => [
                    'type' => self::FILTER_TYPE_STRING,
                ],
            ],
            [
                'key' => 'email_subscription',
                'label' => 'Abonné email',
                'type' => self::COLUMN_TYPE_BOOLEAN,
                'filter' => [
                    'type' => self::FILTER_TYPE_BOOLEAN,
                ],
            ],
            [
                'key' => 'sms_subscription',
                'label' => 'Abonné tel',
                'type' => self::COLUMN_TYPE_BOOLEAN,
                'filter' => [
                    'type' => self::FILTER_TYPE_BOOLEAN,
                ],
            ],
            [
                'key' => 'postal_code',
                'label' => 'Code postal',
                'filter' => [
                    'type' => self::FILTER_TYPE_STRING,
                ],
            ],
            [
                'key' => 'city_code',
                'label' => 'Code commune',
            ],
            [
                'key' => 'city',
                'label' => 'Commune',
                'filter' => [
                    'type' => self::FILTER_TYPE_STRING,
                ],
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
                'filter' => [
                    'type' => self::FILTER_TYPE_SELECT,
                    'options' => [
                        'multiple' => true,
                        'choices' => $this->adherentInterests,
                    ],
                ],
            ],
        ];
    }
}
