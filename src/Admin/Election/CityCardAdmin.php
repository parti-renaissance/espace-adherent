<?php

namespace AppBundle\Admin\Election;

use AppBundle\Form\Admin\Election\CityCandidateType;
use AppBundle\Form\Admin\Election\CityPrevisionType;
use Sonata\AdminBundle\Datagrid\ListMapper;
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

    protected function configureListFields(ListMapper $list)
    {
        parent::configureListFields($list);

        $list
            ->add('preparationPrevision', null, [
                'label' => 'Schéma prévu',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('candidatePrevision', null, [
                'label' => 'Schéma candidat',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('nationalPrevision', null, [
                'label' => 'Schéma arbitré',
                'template' => 'admin/election/city_card/_list_prevision.html.twig',
            ])
            ->add('results', null, [
                'label' => 'Résultats',
                'virtual_field' => true,
                'template' => 'admin/election/city_card/_list_results.html.twig',
            ])
            ->reorder([
                'city.name',
                'city.inseeCode',
                'city.department',
                'city.department.region',
                'preparationPrevision',
                'candidatePrevision',
                'nationalPrevision',
                'results',
                '_action',
            ])
        ;
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/city_card/list.html.twig';
        }

        return parent::getTemplate($name);
    }
}
