<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\AdministratorRoleGroupEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministratorRoleGroupEnumType extends AbstractType
{
    public function getParent(): string
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class' => AdministratorRoleGroupEnum::class,
                'choice_label' => static function (AdministratorRoleGroupEnum $role): string {
                    return 'administrator.role.group_code.'.$role->value;
                },
            ])
        ;
    }
}
