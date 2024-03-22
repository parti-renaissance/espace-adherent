<?php

namespace App\Form\Admin\Procuration;

use App\Procuration\V2\RequestStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestStatusEnumType extends AbstractType
{
    public function getParent()
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => RequestStatusEnum::class,
                'choice_label' => static function (RequestStatusEnum $role): string {
                    return 'procuration.request.status.'.$role->value;
                },
            ])
        ;
    }
}
