<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ApplicationRequest\TechnicalSkill;
use AppBundle\Entity\ApplicationRequest\Theme;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class VolunteerAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'E-mail',
            ])
            ->add('favoriteCities', null, [
                'label' => 'Ville(s) choisie(s)',
                'template' => 'admin/application_requests/_favorite_cities.html.twig',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'E-mail',
            ])
            ->add('address', null, [
                'label' => 'Adresse',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
            ])
            ->add('phone', PhoneNumberType::class, [
                'label' => 'Téléphone',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('profession', null, [
                'label' => 'Profession',
            ])
            ->add('favoriteThemes', EntityType::class, [
                'label' => 'Thèmes favoris',
                'class' => Theme::class,
                'multiple' => true,
            ])
            ->add('customFavoriteTheme', null, [
                'label' => 'Thèmes favoris personnalisés',
            ])
            ->add('technicalSkills', EntityType::class, [
                'label' => 'Compétences techniques',
                'class' => TechnicalSkill::class,
                'multiple' => true,
            ])
            ->add('customTechnicalSkills', null, [
                'label' => 'Compétences techniques personnalisées',
            ])
            ->add('isPreviousCampaignMember', BooleanType::class, [
                'label' => "Fait partie d'une précédente campagne ?",
            ])
            ->add('previousCampaignDetails', null, [
                'label' => 'Détails de la précédente campagne',
            ])
            ->add('shareAssociativeCommitment', BooleanType::class, [
                'label' => "Partage l'engagement associatif ?",
            ])
            ->add('associativeCommitmentDetails', null, [
                'label' => "Détails de l'engagement associatif",
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
        ;
    }
}
