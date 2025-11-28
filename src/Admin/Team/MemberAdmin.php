<?php

declare(strict_types=1);

namespace App\Admin\Team;

use App\Form\Admin\Team\MemberAdherentAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class MemberAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('adherent', MemberAdherentAutocompleteType::class, [
                'model_manager' => $this->getModelManager(),
            ])
        ;
    }
}
