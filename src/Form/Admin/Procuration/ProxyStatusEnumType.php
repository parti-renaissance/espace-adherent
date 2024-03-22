<?php

namespace App\Form\Admin\Procuration;

use App\Procuration\V2\ProxyStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProxyStatusEnumType extends AbstractType
{
    public function getParent()
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => ProxyStatusEnum::class,
                'choice_label' => static function (ProxyStatusEnum $role): string {
                    return 'procuration.proxy.status.'.$role->value;
                },
            ])
        ;
    }
}
