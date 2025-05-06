<?php

namespace App\Admin;

use App\Entity\Adherent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class AgoraMembershipAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'agora';

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('adherent', ModelAutocompleteType::class, [
                'label' => 'Adhérent',
                'minimum_input_length' => 1,
                'items_per_page' => 20,
                'property' => [
                    'search',
                ],
                'to_string_callback' => static function (Adherent $adherent): string {
                    return \sprintf(
                        '%s (%s) [%s]',
                        $adherent->getFullName(),
                        $adherent->getEmailAddress(),
                        $adherent->getId()
                    );
                },
                'model_manager' => $this->getModelManager(),
                'btn_add' => false,
            ])
        ;
    }
}
