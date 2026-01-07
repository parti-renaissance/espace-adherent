<?php

declare(strict_types=1);

namespace App\Form\NationalEvent\PackageField;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SelectFieldFormType extends AbstractFieldFormType
{
    public const FIELD_NAME = 'select';

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
