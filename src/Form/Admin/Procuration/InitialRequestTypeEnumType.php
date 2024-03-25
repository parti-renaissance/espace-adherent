<?php

namespace App\Form\Admin\Procuration;

use App\Procuration\V2\InitialRequestTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitialRequestTypeEnumType extends AbstractType
{
    public function getParent()
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
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
