<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UploadableFileAdmin extends AbstractAdmin
{
    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('uploadFile', VichFileType::class, [
                'label' => false,
                'asset_helper' => true,
            ])
        ;
    }
}
