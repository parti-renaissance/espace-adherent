<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Admin\AdherentAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class AgoraMembershipAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('adherent', AdherentAutocompleteType::class, ['label' => 'Adhérent']);
    }
}
