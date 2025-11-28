<?php

declare(strict_types=1);

namespace App\Form;

use App\Enum\CivilityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CivilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => CivilityEnum::class,
        ]);
    }

    public function getParent(): string
    {
        return EnumType::class;
    }
}
