<?php

namespace App\Admin\Pap;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Pap\Campaign;
use App\Form\Admin\SimpleMDEContent;
use App\Pap\Command\UpdateCampaignAddressInfoCommand;
use App\Scope\ScopeVisibilityEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Messenger\MessageBusInterface;

class CampaignAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Security $security,
    ) {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('show')
            ->remove('delete')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations ⚙️', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('brief', SimpleMDEContent::class, [
                    'label' => 'Brief',
                    'required' => false,
                    'attr' => ['rows' => 15],
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
                    'btn_add' => false,
                ])
            ->end()
        ;

        $form
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
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
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'multiple' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add(ListMapper::NAME_ACTIONS, null, [
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
    protected function prePersist(object $object): void
    {
        $object->setAdministrator($this->security->getUser());
        if ($object->getZones()->count() > 0) {
            $object->setVisibility(ScopeVisibilityEnum::LOCAL);
        }
    }

    /**
     * @param Campaign $object
     */
    protected function postPersist(object $object): void
    {
        parent::postPersist($object);

        $this->bus->dispatch(new UpdateCampaignAddressInfoCommand($object->getUuid()));
    }

    /**
     * @param Campaign $object
     */
    protected function preUpdate(object $object): void
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
    protected function postUpdate(object $object): void
    {
        parent::postUpdate($object);

        $this->bus->dispatch(new UpdateCampaignAddressInfoCommand($object->getUuid()));
    }
}
