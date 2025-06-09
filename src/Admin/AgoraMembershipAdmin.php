<?php

namespace App\Admin;

use App\Entity\Adherent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class AgoraMembershipAdmin extends AbstractAdmin
{
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
                    $label = \sprintf(
                        '%s (%s) [%s]',
                        $adherent->getFullName(),
                        $adherent->getEmailAddress(),
                        $adherent->getPublicId()
                    );

                    if ($adherent->isRenaissanceAdherent()) {
                        $label .= '<span class="label" style="margin-left: 4px;background-color: #00205F;">Adhérent</span>';
                    } else {
                        $label .= '<span class="label" style="margin-left: 4px;background-color: #73C0F1;">Sympathisant</span>';
                    }

                    return $label;
                },
                'safe_label' => true,
                'model_manager' => $this->getModelManager(),
                'btn_add' => false,
            ])
        ;
    }
}
