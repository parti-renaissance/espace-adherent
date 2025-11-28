<?php

declare(strict_types=1);

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType as BaseCKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CkEditorType extends AbstractType
{
    public function getParent(): string
    {
        return BaseCKEditorType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'config' => [
                'versionCheck' => false,
            ],
        ]);
    }
}
