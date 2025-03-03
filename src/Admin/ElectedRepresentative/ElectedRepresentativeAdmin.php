<?php

namespace App\Admin\ElectedRepresentative;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\ElectedRepresentative\Contribution\ContributionStatusEnum;
use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\ElectedRepresentative\ElectedRepresentativeMandatesOrderer;
use App\ElectedRepresentative\UserListDefinitionHistoryManager;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\Form\AdherentEmailType;
use App\Form\AdherentMandateType;
use App\Form\ElectedRepresentative\SponsorshipType;
use App\Form\GenderType;
use App\Form\TelNumberType;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\CollectionType as SonataCollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ElectedRepresentativeAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    private $dispatcher;
    private $userListDefinitionHistoryManager;

    /**
     * @var UserListDefinition[]|array
     */
    private $userListDefinitionsBeforeUpdate;
    private $beforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        UserListDefinitionHistoryManager $userListDefinitionHistoryManager,
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->userListDefinitionHistoryManager = $userListDefinitionHistoryManager;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('show')
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAlias();

        $query
            ->leftJoin("$alias.mandates", 'mandate')
            ->leftJoin("$alias.politicalFunctions", 'politicalFunction')
            ->leftJoin("$alias.labels", 'label')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('currentMandates', null, [
                'label' => 'Mandats actuels',
                'template' => 'admin/elected_representative/list_mandates.html.twig',
            ])
            ->add('currentZones', null, [
                'label' => 'Périmètre(s) géographique(s)',
                'virtual_field' => true,
                'template' => 'admin/elected_representative/list_zones.html.twig',
            ])
            ->add('currentPoliticalFunctions', null, [
                'label' => 'Fonctions actuelles',
                'template' => 'admin/elected_representative/list_political_functions.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Qualifications',
                'virtual_field' => true,
                'template' => 'admin/elected_representative/list_type.html.twig',
            ])
            ->add('contribution', null, [
                'label' => 'Cotisation',
                'virtual_field' => true,
                'template' => 'admin/elected_representative/list_contribution.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/elected_representative/list_actions.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('gender', null, [
                    'label' => 'Civilité',
                ])
                ->add('emailAddress', EmailType::class, [
                    'mapped' => false,
                    'label' => 'Adresse email de l\'adhérent',
                    'template' => 'admin/elected_representative/show_email.html.twig',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre email de contact',
                ])
                ->add('phone', null, [
                    'mapped' => false,
                    'label' => 'Téléphone',
                    'template' => 'admin/elected_representative/show_phone.html.twig',
                ])
                ->add('contactPhone', null, [
                    'label' => 'Autre téléphone de contact',
                    'template' => 'admin/elected_representative/show_contact_phone.html.twig',
                ])
                ->add('birthDate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'label' => 'Lieu de naissance',
                ])
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('gender', GenderType::class, [
                    'label' => 'Civilité',
                    'placeholder' => 'common.gender.unknown',
                    'required' => false,
                    'choices' => [
                        'common.gender.woman' => Genders::FEMALE,
                        'common.gender.man' => Genders::MALE,
                    ],
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('adherent', AdherentEmailType::class, [
                    'required' => false,
                    'label' => 'Adresse email',
                    'help' => 'Attention, changer l\'e-mail ici fera que l\'élu sera associé à un autre compte adhérent.'
                        .' Si vous souhaitez ajouter un autre email de contact, faites-le ci-dessous.',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre email de contact',
                    'required' => false,
                ])
                ->add('contactPhone', TelNumberType::class, [
                    'required' => false,
                    'label' => 'Autre téléphone de contact',
                    'attr' => ['class' => 'phone'],
                ])
                ->add('birthDate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'required' => false,
                    'label' => 'Lieu de naissance',
                ])
        ;

        if ($this->isGranted('LABELS')) {
            $form->add('userListDefinitions', null, [
                'label' => 'Labels',
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('uld')
                        ->andWhere('uld.type IN (:type)')
                        ->setParameter('type', [UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE])
                        ->orderBy('uld.label', 'ASC')
                    ;
                },
            ]);
        }

        $form
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
            ->end()
            ->with(
                'Réseaux sociaux',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('socialNetworkLinks', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.social_network_link',
                ])
            ->end()
            ->with(
                'Étiquettes',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('labels', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.label',
                ])
            ->end()
            ->with(
                'Parrainages',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('sponsorships', CollectionType::class, [
                    'entry_type' => SponsorshipType::class,
                    'label' => false,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Mandats')
                ->add('mandates', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.mandate',
                ])
            ->end()
            ->with('Fonctions')
                ->add('politicalFunctions', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.political_function',
                ])
            ->end()
        ;

        $form->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event): void
    {
        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $event->getData();

        // change mandates order
        if (!$electedRepresentative->getMandates()->isEmpty()) {
            ElectedRepresentativeMandatesOrderer::updateOrder($electedRepresentative->getMandates());
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('mandatesType', CallbackFilter::class, [
                'label' => 'Mandats',
                'show_filter' => true,
                'field_type' => AdherentMandateType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value->getValue() as $mandate) {
                        $where->add("mandate.type = :mandate_$mandate");
                        $qb->setParameter("mandate_$mandate", $mandate);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('mandatesOnGoing', CallbackFilter::class, [
                'label' => 'Mandat en cours ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere('mandate.onGoing = 1');

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere('mandate.onGoing = 0');

                            break;
                    }

                    return true;
                },
            ])
            ->add('politicalFunctionsName', CallbackFilter::class, [
                'label' => 'Fonctions',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => PoliticalFunctionNameEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value->getValue() as $politicalFunctions) {
                        $where->add("politicalFunction.name = :function_$politicalFunctions");
                        $qb->setParameter("function_$politicalFunctions", $politicalFunctions);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('politicalFunctionsOnGoing', CallbackFilter::class, [
                'label' => 'Fonction en cours ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere('politicalFunction.onGoing = 1');

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere('politicalFunction.onGoing = 0');

                            break;
                    }

                    return true;
                },
            ])
            ->add('mandates.geoZone', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
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
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'global.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere("$alias.adherent IS NOT NULL");

                            return true;
                        case 'no':
                            $qb->andWhere("$alias.adherent IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('userListDefinitions', ModelFilter::class, [
                'label' => 'Labels',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 0,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'label',
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $qb = $datagrid->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb
                            ->andWhere($alias.'.type IN (:type)')
                            ->setParameter('type', [UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE])
                            ->orderBy($alias.'.label', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
            ])
            ->add('labelsName', CallbackFilter::class, [
                'label' => 'Étiquettes',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => LabelNameEnum::ALL,
                    'multiple' => true,
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere('label.name IN (:label_names)');
                    $qb->setParameter('label_names', $value->getValue());

                    return true;
                },
            ])
            ->add('revenueDeclared', CallbackFilter::class, [
                'label' => 'Revenus déclarés ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $alias = $qb->getRootAlias();
                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.contributedAt IS NOT NULL");

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere("$alias.contributedAt IS NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('contributionEligible', CallbackFilter::class, [
                'label' => 'Éligible à la cotisation ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $alias = $qb->getRootAlias();
                    $qb->andWhere("$alias.contributionStatus = :contribution_status");
                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->setParameter('contribution_status', ContributionStatusEnum::ELIGIBLE);

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->setParameter('contribution_status', ContributionStatusEnum::NOT_ELIGIBLE);

                            break;
                    }

                    return true;
                },
            ])
        ;
    }

    /**
     * @param ElectedRepresentative $object
     */
    protected function alterObject(object $object): void
    {
        if (null === $this->userListDefinitionsBeforeUpdate) {
            $this->userListDefinitionsBeforeUpdate = $object->getUserListDefinitions()->toArray();
        }

        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $object;
        }
    }

    /**
     * @param ElectedRepresentative $object
     */
    protected function preUpdate(object $object): void
    {
        if ($this->beforeUpdate) {
            $this->dispatcher->dispatch(new ElectedRepresentativeEvent($this->beforeUpdate), ElectedRepresentativeEvents::BEFORE_UPDATE);
        }

        parent::preUpdate($object);

        $this->userListDefinitionHistoryManager->handleChanges($object, $this->userListDefinitionsBeforeUpdate);
    }

    /**
     * @param ElectedRepresentative $object
     */
    protected function postUpdate(object $object): void
    {
        parent::postUpdate($object);

        $this->dispatcher->dispatch(new ElectedRepresentativeEvent($object), ElectedRepresentativeEvents::POST_UPDATE);
    }

    protected function configureExportFields(): array
    {
        return [
            'Nom' => 'lastName',
            'Prénom' => 'firstName',
            'Mandats actuels (nuance politique)' => 'exportMandates',
            'Fonctions actuelles' => 'exportPoliticalFunctions',
            'Adhérent' => 'exportIsAdherent',
        ];
    }
}
