<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Sonata\AdminBundle\Form\Type\AdminType as SonataAdminType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminType extends AbstractType
{
    public function getParent(): string
    {
        return SonataAdminType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => null,
            ])
            ->setAllowedTypes('form_type', 'string')
        ;
    }
}
