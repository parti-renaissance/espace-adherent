<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\Theme;
use AppBundle\Form\GenderType;
use League\Flysystem\Filesystem;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class ApplicationRequestRunningMateRequestAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];
    private $storage;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('gender', 'trans', [
                'label' => 'Genre',
                'format' => 'common.gender.%s',
            ])
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
                'template' => 'admin/application_request/show_favorite_cities.html.twig',
            ])
            ->add('curriculum', null, [
                'label' => 'CV',
                'template' => 'admin/running_mate/show_curriculum.html.twig',
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
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
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
                    'label' => 'Code INSEE',
                ])
                ->add('cityName', null, [
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
                ->add('favoriteThemeDetails', null, [
                    'label' => 'Pourquoi avez-vous choisi cette thématique ?',
                ])
                ->add('removeCurriculum', CheckboxType::class, [
                    'label' => 'Supprimer le CV ?',
                    'required' => false,
                ])
                ->add('isLocalAssociationMember', BooleanType::class, [
                    'label' => 'Êtes-vous engagé dans une/des association(s) locale(s) ?',
                ])
                ->add('localAssociationDomain', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('isPoliticalActivist', BooleanType::class, [
                    'label' => 'Avez-vous déjà eu un engagement militant ?',
                ])
                ->add('politicalActivistDetails', null, [
                    'label' => 'Si oui, n\'hésitez pas à préciser',
                ])
                ->add('isPreviousElectedOfficial', BooleanType::class, [
                    'label' => 'Avez-vous déjà exercé un mandat ?',
                ])
                ->add('previousElectedOfficialDetails', null, [
                    'label' => 'Si oui, précisez',
                ])
                ->add('projectDetails', null, [
                    'label' => 'Quel projet pour votre commune souhaiteriez-vous contribuer à porter ?',
                ])
                ->add('professionalAssets', null, [
                    'label' => 'Quel sont les atouts de votre parcours professionnel ?',
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

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function preUpdate($runningMateRequest)
    {
        parent::preUpdate($runningMateRequest);

        if ($runningMateRequest->getRemoveCurriculum()) {
            $this->storage->delete($runningMateRequest->getPathWithDirectory());
            $runningMateRequest->removeCurriculumName();
        }
    }

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function postRemove($runningMateRequest)
    {
        parent::postRemove($runningMateRequest);

        if ($this->storage->has($runningMateRequest->getPathWithDirectory())) {
            $this->storage->delete($runningMateRequest->getPathWithDirectory());
        }
    }

    public function setStorage(Filesystem $storage): void
    {
        $this->storage = $storage;
    }

    public function getExportFields()
    {
        return [
            'UUID' => 'uuid',
            'Genre' => 'gender',
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
            'Détails sur thématique(s) choisie(s)' => 'favoriteThemeDetails',
            'Engagé dans une/des association(s) locale(s) ?' => 'isLocalAssociationMemberAsString',
            'Précisions sur l\'engagement dans une/des association(s)' => 'localAssociationDomain',
            'Engagement militant ?' => 'isPoliticalActivistAsString',
            'Précisions sur l\'engagement militant' => 'politicalActivistDetails',
            'Déjà exercé un mandat ?' => 'isPreviousElectedOfficialAsString',
            'Précisions sur le(s) mandat(s)' => 'previousElectedOfficialDetails',
            'Projet(s) pour la commune' => 'projectDetails',
            'Atouts professionels' => 'professionalAssets',
        ];
    }
}
