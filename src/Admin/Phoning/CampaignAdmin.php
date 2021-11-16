<?php

namespace App\Admin\Phoning;

use App\Admin\AbstractAdmin;
use App\Admin\Audience\AudienceAdmin;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Phoning\Campaign;
use App\Entity\Team\Team;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\Team\MemberAdherentAutocompleteType;
use App\Form\Audience\AudienceSnapshotType;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;

class CampaignAdmin extends AbstractAdmin
{
    private Security $security;

    public function getFormBuilder()
    {
        if (!$this->isPermanent()) {
            $this->formOptions['validation_groups'] = ['Default', 'regular_campaign'];
        }

        return parent::getFormBuilder();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations ⚙️')
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
                    'help' => 'Cet objectif sera affiché de manière identique à chaque appelant. L’objectif de la campagne sera calculé en multipliant l’objectif individuel par le nombre d’appelants.',
                ])
                ->add('survey', EntityType::class, [
                    'label' => 'Questionnaire national',
                    'placeholder' => '--',
                    'class' => NationalSurvey::class,
                    'choice_label' => 'name',
                ])
        ;

        if (!$this->isPermanent()) {
            $formMapper->add('finishAt', DatePickerType::class, [
                'label' => 'Date de fin',
                'error_bubbling' => true,
                'attr' => ['class' => 'width-140'],
            ]);
        }

        $formMapper
                ->add('team', EntityType::class, [
                    'label' => 'Équipe phoning',
                    'class' => Team::class,
                    'choice_label' => function (Team $team) {
                        return sprintf('%s (%s)',
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
            ->end()
        ;

        if (!$this->isPermanent()) {
            $formMapper->with('Filtres')
                ->add('audience', AudienceSnapshotType::class, ['label' => false])
            ->end()
            ;

            $formMapper->get('audience')->add('zones', AdminZoneAutocompleteType::class, [
                'required' => false,
                'multiple' => true,
                'model_manager' => $this->getModelManager(),
                'admin_code' => AudienceAdmin::SERVICE_CODE,
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('title', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('team', ModelAutocompleteFilter::class, [
                'label' => 'Équipe',
                'show_filter' => true,
                'field_options' => [
                    'property' => [
                        'name',
                    ],
                ],
            ])
            ->add('team.members.adherent', ModelAutocompleteFilter::class, [
                'label' => 'Appelant',
                'show_filter' => true,
                'field_type' => MemberAdherentAutocompleteType::class,
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
            ->add('campaignHistoriesCount', null, [
                'label' => 'Appels passés',
                'template' => 'admin/phoning/campaign/list_campaign_histories_count.html.twig',
            ])
            ->add('campaignHistoriesWithDataSurveyCount', null, [
                'label' => 'Questionnaires remplis',
                'template' => 'admin/phoning/campaign/list_campaign_histories_with_data_survey_count.html.twig',
            ])
            ->add('unjoinAndUnsubscribeCount', null, [
                'label' => 'Désabonnements / Désadhésions',
                'template' => 'admin/phoning/campaign/list_campaign_histories_unjoin_unsubscribe_count.html.twig',
            ])
            ->add('_action', null, [
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

    /**
     * @param Campaign $object
     */
    public function prePersist($object)
    {
        $object->setAdministrator($this->security->getUser());
    }

    /** @required */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    public function toString($object): string
    {
        return sprintf('%s%s', $object, $object->isPermanent() ? ' [Campagne permanente]' : '');
    }

    private function isPermanent(): bool
    {
        return $this->getSubject()->isPermanent();
    }
}
