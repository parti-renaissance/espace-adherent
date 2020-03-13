<?php

namespace AppBundle\Admin\Election;

use AppBundle\Form\Admin\Election\CityCandidateType;
use AppBundle\Form\Admin\Election\CityPrevisionType;
use Sonata\AdminBundle\Form\FormMapper;

class CityCardAdmin extends AbstractCityCardAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);

        $form
            ->add('firstCandidate', CityCandidateType::class, [
                'required' => false,
            ])
            ->add('preparationPrevision', CityPrevisionType::class, [
                'label' => 'Préparation',
                'required' => false,
            ])
            ->add('candidatePrevision', CityPrevisionType::class, [
                'label' => 'Position candidat',
                'required' => false,
            ])
            ->add('nationalPrevision', CityPrevisionType::class, [
                'label' => 'Arbitrage national',
                'required' => false,
            ])
            ->add('partners', 'sonata_type_collection', [
                'by_reference' => false,
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'admin_code' => 'app.admin.election_city_card_partner',
            ])
            ->add('contacts', 'sonata_type_collection', [
                'by_reference' => false,
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'admin_code' => 'app.admin.election_city_card_contact',
            ])
        ;
    }
}
