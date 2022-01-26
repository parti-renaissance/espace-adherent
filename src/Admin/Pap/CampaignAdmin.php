<?php

namespace App\Admin\Pap;

use App\Admin\AbstractAdmin;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Pap\Campaign;
use App\Pap\Command\UpdateCampaignAddressInfoCommand;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                ])
            ->end()
            ->with('Filtre - Ciblage par bureau de vote', ['class' => 'col-md-6'])
                ->add('deltaPredictionAndResultMin2017', PercentType::class, [
                    'label' => 'Ecart entre prédiction et résultat 2017 - Min',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaPredictionAndResultMax2017', PercentType::class, [
                    'label' => 'Ecart entre prédiction et résultat 2017 - Max',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaAveragePredictionsMin', PercentType::class, [
                    'label' => 'Ecart prediction et prediction moyennes - Min',
                    'scale' => 2,
                    'required' => false,
                ])
                ->add('deltaAveragePredictionsMax', PercentType::class, [
                    'label' => 'Ecart prediction et prediction moyennes - Max',
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
                    'label' => 'Liste des Priorité de mal inscrits - Min',
                    'required' => false,
                ])
                ->add('misregistrationsPriorityMax', IntegerType::class, [
                    'label' => 'Liste des Priorité de mal inscrits - Max',
                    'required' => false,
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
