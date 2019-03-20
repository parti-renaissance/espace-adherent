<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ElectedRepresentativesRegisterAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('nom', null, [
                'label' => 'Nom',
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
            ])
            ->add('dateNaissance', null, [
                'label' => 'Date de naissance',
                'pattern' => 'd/M/Y',
            ])
            ->add('nomProfession', null, [
                'label' => 'Profession',
            ])
            ->add('nuancePolitique', null, [
                'label' => 'Nuance politique',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/elected_representatives_register/field_adherent.html.twig',
            ])
            ->add('typeElu', 'bool', [
                'label' => 'Mandats',
            ])
            ->add('communeNom', null, [
                'label' => 'Ville',
            ])
            ->add('epciSiren', null, [
                'label' => 'EPCI',
            ])
            ->add('cantonNom', null, [
                'label' => 'Canton',
            ])
            ->add('circoLegisNom', null, [
                'label' => 'Circonscription',
            ])
            ->add('dptNom', null, [
                'label' => 'Département',
            ])
            ->add('dateDebutMandat', null, [
                'label' => 'Date élection',
            ])
            ->add('nomFonction', null, [
                'label' => 'Fonction',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('nom', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('nuancePolitique', null, [
                'label' => 'Nuance politique',
            ])
            ->add('typeElu', null, [
                'label' => 'Mandat',
            ])
            ->add('communeNom', null, [
                'label' => 'Ville',
            ])
            ->add('dptNom', null, [
                'label' => 'Département',
            ])
        ;
    }
}
