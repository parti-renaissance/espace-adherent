<?php

namespace App\Admin\Pap;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Pap\Campaign;
use App\Pap\Command\UpdateCampaignAddressInfoCommand;
use App\Scope\ScopeVisibilityEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class CampaignAdmin extends AbstractAdmin
{
    private Security $security;
    private MessageBusInterface $bus;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('show')
            ->remove('delete')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations ⚙️', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('brief', TextareaType::class, [
                    'label' => 'Brief',
                    'required' => false,
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 15],
                ])
                ->add('goal', IntegerType::class, [
                    'attr' => ['min' => 1],
                    'label' => 'Objectif individuel',
                    'help' => 'Cet objectif sera affiché de manière identique à chaque militant.',
                ])
                ->add('beginAt', DatePickerType::class, [
                    'label' => 'Date de début',
                    'error_bubbling' => true,
                    'attr' => ['class' => 'width-140'],
                ])
                ->add('finishAt', DatePickerType::class, [
                    'label' => 'Date de fin',
                    'error_bubbling' => true,
                    'attr' => ['class' => 'width-140'],
                ])
                ->add('zones', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'required' => false,
                    'multiple' => true,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                ])
            ->end()
            ->with('Filtre - Ciblage par bureau de vote', ['class' => 'col-md-6'])
                ->add('deltaPredictionAndResultMin2017', PercentType::class, [
                    'label' => 'Écart entre prédiction et résultat 2017 - Min',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaPredictionAndResultMax2017', PercentType::class, [
                    'label' => 'Écart entre prédiction et résultat 2017 - Max',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaAveragePredictionsMin', PercentType::class, [
                    'label' => 'Écart prediction et prediction moyenne - Min',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaAveragePredictionsMax', PercentType::class, [
                    'label' => 'Écart prediction et prediction moyenne - Max',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('abstentionsMin2017', PercentType::class, [
                    'label' => 'Taux d\'abstention 2017 - Min',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('abstentionsMax2017', PercentType::class, [
                    'label' => 'Taux d\'abstention 2017 - Max',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('misregistrationsPriorityMin', IntegerType::class, [
                    'label' => 'Liste des priorités de mal-inscrits - Min',
                    'required' => false,
                ])
                ->add('misregistrationsPriorityMax', IntegerType::class, [
                    'label' => 'Liste des priorités de mal-inscrits - Max',
                    'required' => false,
                ])
                ->add('firstRoundPriority', IntegerType::class, [
                    'label' => 'Priorité 1er tour',
                    'required' => false,
                    'help' => 'Indiquer la priorité minimum des bureaux de vote à cibler',
                ])
                ->add('secondRoundPriority', IntegerType::class, [
                    'label' => 'Priorité 2ème tour',
                    'required' => false,
                    'help' => 'Indiquer la priorité minimum des bureaux de vote à cibler',
                ])
            ->end()
        ;

        $formMapper
            ->with('Questionnaire')
                ->add('survey', EntityType::class, [
                    'label' => 'Questionnaire national',
                    'placeholder' => '--',
                    'class' => NationalSurvey::class,
                    'choice_label' => 'name',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $admin = $filter->getAdmin();

        $filter
            ->add('title', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('beginAt', DateRangeFilter::class, [
                'label' => 'Date de début',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('visibility', ChoiceFilter::class, [
                'label' => 'Visibilité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ScopeVisibilityEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "scope.visibility.$choice";
                    },
                ],
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Zones',
                'field_options' => [
                    'model_manager' => $admin->getModelManager(),
                    'admin_code' => $admin->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('survey', null, [
                'label' => 'Questionnaire',
            ])
            ->add('goal', null, [
                'label' => 'Objectif de la campagne',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('visibility', null, [
                'label' => 'Visibilité',
                'template' => 'admin/scope/list_visibility.html.twig',
            ])
            ->add('zones', null, [
                'label' => 'Zone',
                'template' => 'admin/scope/list_zones.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'visited_doors' => [
                        'template' => 'admin/pap/campaign/list_action_visited_doors.html.twig',
                    ],
                ],
            ])
        ;
    }

    /**
     * @param Campaign $object
     */
    public function prePersist($object)
    {
        $object->setAdministrator($this->security->getUser());
        if ($object->getZones()->count() > 0) {
            $object->setVisibility(ScopeVisibilityEnum::LOCAL);
        }
    }

    /**
     * @param Campaign $object
     */
    public function postPersist($object)
    {
        parent::postPersist($object);

        $this->bus->dispatch(new UpdateCampaignAddressInfoCommand($object->getUuid()));
    }

    /**
     * @param Campaign $object
     */
    public function preUpdate($object)
    {
        if ($object->getZones()->count() > 0) {
            $object->setVisibility(ScopeVisibilityEnum::LOCAL);
        } else {
            $object->setVisibility(ScopeVisibilityEnum::NATIONAL);
        }
    }

    /**
     * @param Campaign $object
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);

        $this->bus->dispatch(new UpdateCampaignAddressInfoCommand($object->getUuid()));
    }

    /** @required */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /** @required */
    public function setBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }
}
