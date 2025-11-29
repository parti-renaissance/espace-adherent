<?php

declare(strict_types=1);

namespace App\Form\Admin\Procuration;

use App\Procuration\V2\InitialRequestTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitialRequestTypeEnumType extends AbstractType
{
    public function getParent(): string
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class' => InitialRequestTypeEnum::class,
                'choice_label' => static function (InitialRequestTypeEnum $type): string {
                    return 'procuration.initial_request.type.'.$type->value;
                },
            ])
        ;
    }
}
