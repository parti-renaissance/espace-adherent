<?php

namespace AppBundle\Admin;

use AppBundle\Repository\ElectedRepresentativesRegisterRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ElectedRepresentativesRegisterAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    private $repository;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        ElectedRepresentativesRegisterRepository $repository
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->repository = $repository;
    }

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
            ->add('nuancePolitique', ChoiceFilter::class, [
                'label' => 'Nuance politique',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->repository->findAllNuancePolitiqueValues(),
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ],
            ])
            ->add('typeElu', ChoiceFilter::class, [
                'label' => 'Mandat',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->repository->findAllTypeEluValues(),
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ],
            ])
            ->add('communeNom', null, [
                'label' => 'Ville',
            ])
            ->add('dptNom', null, [
                'label' => 'Département',
            ])
            ->add('nomFonction', ChoiceFilter::class, [
                'label' => 'Fonction',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->repository->findAllNomFonctionValues(),
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ],
            ])
        ;
    }
}
