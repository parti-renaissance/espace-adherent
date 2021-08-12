<?php

namespace App\ManagedUsers;

use App\ValueObject\Genders;

class ColumnsConfigurator
{
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
                'filter' => [
                    'type' => 'select',
                    'options' => [
                        'choices' => Genders::CHOICES_LABELS,
                    ],
                ],
            ],
            [
                'key' => 'first_name',
                'label' => 'Prénom',
                'filter' => [
                    'type' => 'text',
                ],
            ],
            [
                'key' => 'last_name',
                'label' => 'Nom',
                'filter' => [
                    'type' => 'text',
                ],
            ],
            [
                'key' => 'email_subscription',
                'label' => 'Abonné email',
                'filter' => [
                    'type' => 'boolean',
                ],
            ],
            [
                'key' => 'sms_subscription',
                'label' => 'Abonné tel',
                'filter' => [
                    'type' => 'boolean',
                ],
            ],
            [
                'key' => 'postal_code',
                'label' => 'Code postal',
                'filter' => [
                    'type' => 'string',
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
                    'type' => 'string',
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
                'filter' => [
                    'type' => 'select',
                    'options' => [
                        'choices' => $this->adherentInterests,
                    ],
                ],
            ],
        ];
    }
}
