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
                'key' => 'firstName',
                'label' => 'Prénom',
                'filter' => [
                    'type' => 'text',
                ],
            ],
            [
                'key' => 'lastName',
                'label' => 'Nom',
                'filter' => [
                    'type' => 'text',
                ],
            ],
            [
                'key' => 'subscribedEmail',
                'label' => 'Abonné email',
                'filter' => [
                    'type' => 'boolean',
                ],
            ],
            [
                'key' => 'subscribedPhone',
                'label' => 'Abonné tel',
                'filter' => [
                    'type' => 'boolean',
                ],
            ],
            [
                'key' => 'postalCode',
                'label' => 'Code postal',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'cityCode',
                'label' => 'Code commune',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'city',
                'label' => 'Commune',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'departmentCode',
                'label' => 'Code département',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'department',
                'label' => 'Département',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'regionCode',
                'label' => 'Code région',
                'filter' => [
                    'type' => 'string',
                ],
            ],
            [
                'key' => 'region',
                'label' => 'Région',
                'filter' => [
                    'type' => 'string',
                ],
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
