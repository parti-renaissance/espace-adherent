<?php

declare(strict_types=1);

namespace App\Admin\Phoning;

use App\Admin\AbstractAdmin;
use App\Admin\Audience\AudienceAdmin;
use App\Entity\Adherent;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\Campaign;
use App\Entity\Team\Team;
use App\Form\Admin\AdherentAutocompleteType;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\SimpleMDEContent;
use App\Form\Audience\AudienceSnapshotType;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CampaignAdmin extends AbstractAdmin
{
    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
        parent::__construct();
    }

    protected function configureFormOptions(array &$formOptions): void
    {
        if (!$this->isPermanent()) {
            $formOptions['validation_groups'] = ['Default', 'regular_campaign'];
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations ⚙️')
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
                    'help' => 'Cet objectif sera affiché de manière identique à chaque appelant. L’objectif de la campagne sera calculé en multipliant l’objectif individuel par le nombre d’appelants.',
                ])
                ->add('survey', EntityType::class, [
                    'label' => 'Questionnaire',
                    'placeholder' => '--',
                    'class' => Survey::class,
                    'choice_label' => 'name',
                ])
        ;

        if (!$this->isPermanent()) {
            $form->add('finishAt', DatePickerType::class, [
                'label' => 'Date de fin',
                'error_bubbling' => true,
                'attr' => ['class' => 'width-140'],
            ]);
        }

        $form
                ->add('team', EntityType::class, [
                    'label' => 'Équipe phoning',
                    'class' => Team::class,
                    'choice_label' => function (Team $team) {
                        return \sprintf('%s (%s)',
                            $team->getName(),
                            $team->getMembersCount()
                        );
                    },
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('team')
                            ->innerJoin('team.members', 'member')
                        ;
                    },
                    'required' => !$this->isPermanent(),
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                ])
            ->end()
        ;

        if (!$this->isPermanent()) {
            $form->with('Filtres')
                ->add('audience', AudienceSnapshotType::class, ['label' => false])
            ->end()
            ;

            $form->get('audience')
                ->add('zones', AdminZoneAutocompleteType::class, [
                    'required' => false,
                    'multiple' => true,
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => AudienceAdmin::SERVICE_CODE,
                ])
                ->add('hasSmsSubscription', BooleanType::class, [
                    'transform' => true,
                    'label' => 'Abonné aux SMS',
                    'required' => true,
                ])
            ;
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('team', ModelFilter::class, [
                'label' => 'Équipe',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => 'name',
                ],
            ])
            ->add('team.members.adherent', ModelFilter::class, [
                'label' => 'Appelant',
                'show_filter' => true,
                'field_type' => AdherentAutocompleteType::class,
                'field_options' => [
                    'class' => Adherent::class,
                    'model_manager' => $this->getModelManager(),
                ],
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
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
            ->add('team', null, [
                'label' => 'Équipe',
                'template' => 'admin/phoning/campaign/list_team.html.twig',
            ])
            ->add('survey', null, [
                'label' => 'Questionnaire',
            ])
            ->add('goal', null, [
                'label' => 'Objectif de la campagne',
                'template' => 'admin/phoning/campaign/list_goal.html.twig',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('participantsCount', null, [
                'label' => 'Potentiels participants',
            ])
            ->add('campaignHistoriesCount', null, [
                'label' => 'Appels passés',
                'template' => 'admin/phoning/campaign/list_campaign_histories_count.html.twig',
            ])
            ->add('campaignHistoriesWithDataSurveyCount', null, [
                'label' => 'Questionnaires remplis',
                'virtual_field' => true,
                'template' => 'admin/phoning/campaign/list_campaign_histories_with_data_survey_count.html.twig',
            ])
            ->add('unjoinAndUnsubscribeCount', null, [
                'label' => 'Désabonnements / Désadhésions',
                'virtual_field' => true,
                'template' => 'admin/phoning/campaign/list_campaign_histories_unjoin_unsubscribe_count.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'stats' => [
                        'template' => 'admin/phoning/campaign/list_action_stats.html.twig',
                    ],
                ],
            ])
        ;
    }

    /** @param Campaign $object */
    protected function postPersist(object $object): void
    {
        $this->updateParticipantsCount($object);
        $this->getModelManager()->update($object);
    }

    /** @param Campaign $object */
    protected function preUpdate(object $object): void
    {
        $this->updateParticipantsCount($object);
    }

    private function updateParticipantsCount(Campaign $object): void
    {
        $object->setParticipantsCount((int) $this->adherentRepository->findForPhoningCampaign($object)->getTotalItems());
    }

    public function toString($object): string
    {
        return \sprintf('%s%s', $object, $object->isPermanent() ? ' [Campagne permanente]' : '');
    }

    private function isPermanent(): bool
    {
        return $this->getSubject()->isPermanent();
    }
}
