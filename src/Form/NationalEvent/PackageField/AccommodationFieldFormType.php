<?php

declare(strict_types=1);

namespace App\Form\NationalEvent\PackageField;

class AccommodationFieldFormType extends AbstractFieldFormType
{
    public const FIELD_NAME = 'accommodation';

    public function getParent(): string
    {
        return RadioFieldFormType::class;
    }
}
