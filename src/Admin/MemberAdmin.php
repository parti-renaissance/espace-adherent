<?php

namespace App\Admin;

use App\Form\Admin\MemberAdherentAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class MemberAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('adherent', MemberAdherentAutocompleteType::class, [
                'model_manager' => $this->getModelManager(),
            ])
        ;
    }
}
