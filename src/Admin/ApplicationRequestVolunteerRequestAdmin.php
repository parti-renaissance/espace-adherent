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

class ApplicationRequestVolunteerRequestAdmin extends AbstractAdmin
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
            ->add('favoriteCitiesNames', null, [
                'label' => 'Ville(s) choisie(s)',
                'template' => 'admin/application_request/show_favorite_cities.html.twig',
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
            ->with('Informations personnelles')
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
            ->end()
            ->with('Candidature')
                ->add('profession', null, [
                    'label' => 'Quelle est votre profession ?',
                ])
                ->add('favoriteThemes', EntityType::class, [
                    'label' => 'Vos thématique(s) de prédilection',
                    'class' => Theme::class,
                    'multiple' => true,
                ])
                ->add('customFavoriteTheme', null, [
                    'label' => 'Autre(s) thématique(s) de prédilection',
                ])
                ->add('technicalSkills', EntityType::class, [
                    'label' => 'Disposez-vous de compétences techniques spécifiques ?',
                    'class' => TechnicalSkill::class,
                    'multiple' => true,
                ])
                ->add('customTechnicalSkills', null, [
                    'label' => 'Autres compétences techniques',
                ])
                ->add('isPreviousCampaignMember', BooleanType::class, [
                    'label' => 'Avez-vous déjà participé à une campagne ?',
                ])
                ->add('previousCampaignDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('shareAssociativeCommitment', BooleanType::class, [
                    'label' => 'Souhaitez-vous nous faire part de vos engagements associatifs et/ou militants ?',
                ])
                ->add('associativeCommitmentDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
        ;
    }

    public function getExportFields()
    {
        return [
            'UUID' => 'uuid',
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Email' => 'emailAddress',
            'Ville(s) demandée(s)' => 'getFavoriteCitiesAsString',
            'Téléphone' => 'phone',
            'Adresse' => 'address',
            'Code postal' => 'postalCode',
            'Ville' => 'cityName',
            'Pays' => 'country',
            'Profession' => 'profession',
            'Thématique(s) de prédilection' => 'getFavoriteThemesAsString',
            'Compétences techniques' => 'getTechnicalSkillsAsString',
            'Déjà participé à une campagne ?' => 'isPreviousCampaignMemberAsString',
            'Précisions sur la dernière campagne' => 'previousCampaignDetails',
            'Souhaite faire part de ses engagements' => 'shareAssociativeCommitmentAsString',
            'Précisions sur les engagements' => 'associativeCommitmentDetails',
        ];
    }
}
