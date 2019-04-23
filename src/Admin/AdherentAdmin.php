<?php

namespace AppBundle\Admin;

use AppBundle\Adherent\AdherentRoleEnum;
use AppBundle\Admin\Filter\ReferentTagAutocompleteFilter;
use AppBundle\Coordinator\CoordinatorAreaSectors;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentTag;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\Admin\CoordinatorManagedAreaType;
use AppBundle\Form\Admin\ReferentManagedAreaType;
use AppBundle\Form\EventListener\ReferentManagedAreaListener;
use AppBundle\Form\GenderType;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\Mandates;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class AdherentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'registeredAt',
    ];

    protected $accessMapping = [
        'ban' => 'BAN',
    ];

    private $dispatcher;
    private $emailSubscriptionHistoryManager;

    /**
     * State of adherent data before update
     *
     * @var Adherent
     */
    private $beforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->emailSubscriptionHistoryManager = $emailSubscriptionHistoryManager;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('ban', $this->getRouterIdParameter().'/ban')
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        if ('ban' === $action) {
            $actions = parent::configureActionButtons('show', $object);
        } else {
            $actions = parent::configureActionButtons($action, $object);
        }

        if (\in_array($action, ['edit', 'show', 'ban'], true)) {
            $actions['switch_user'] = ['template' => 'admin/adherent/action_button_switch_user.html.twig'];
        }

        if (\in_array($action, ['edit', 'show'], true)) {
            if ($this->canAccessObject('ban', $object) && $this->hasRoute('ban')) {
                $actions['ban'] = ['template' => 'admin/adherent/action_button_ban.html.twig'];
            }
        }

        return $actions;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('status', null, [
                    'label' => 'Etat du compte',
                ])
                ->add('tags', null, [
                    'label' => 'Tags admin',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('customGender', null, [
                    'label' => 'Personnalisez votre genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('nickname', null, [
                    'label' => 'Pseudo',
                ])
                ->add('nicknameUsed', null, [
                    'label' => 'Pseudo utilisé ?',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                    'template' => 'admin/adherent/show_phone.html.twig',
                ])
                ->add('birthdate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('position', null, [
                    'label' => 'Statut',
                ])
                ->add('mandates', null, [
                    'label' => 'adherent.mandate.admin.label',
                    'template' => 'admin/adherent/show_mandates.html.twig',
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Abonné aux notifications via e-mail et mobile',
                    'associated_property' => 'label',
                ])
            ->end()
            ->with('Responsabilités locales', ['class' => 'col-md-3'])
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                ])
                ->add('coordinatorCommitteeArea', null, [
                    'label' => 'Coordinateur régional',
                ])
                ->add('managedArea.tags', null, [
                    'label' => 'referent.label.tags',
                ])
                ->add('procurationManagedAreaCodesAsString', null, [
                    'label' => 'Responsable procurations',
                ])
                ->add('isAssessorManager', 'boolean', [
                    'label' => 'Est responsable assesseur ?',
                ])
                ->add('assessorManagedAreaCodesAsString', null, [
                    'label' => 'Responsable assesseurs',
                ])
                ->add('coordinatorCitizenProjectArea', null, [
                    'label' => 'coordinator.label.codes.cp',
                ])
            ->end()
            ->with('Mandat électif', ['class' => 'col-md-3'])
                ->add('isDeputy', 'boolean', [
                    'label' => 'Est un(e) député(e) ?',
                ])
                ->add('managedDistrict.name', null, [
                    'label' => 'Circonscription député',
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-3'])
                ->add('isCoordinator', 'boolean', [
                    'label' => 'Est coordinateur ?',
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('isProcurationManager', 'boolean', [
                    'label' => 'Est responsable procuration ?',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
                ->add('tags', 'sonata_type_model', [
                    'label' => 'Tags admin',
                    'multiple' => true,
                    'by_reference' => false,
                    'btn_add' => false,
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('customGender', TextType::class, [
                    'required' => false,
                    'label' => 'Personnalisez votre genre',
                    'attr' => [
                        'max' => 80,
                    ],
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                    'filter_emojis' => true,
                    'format_identity_case' => true,
                ])
                ->add('firstName', TextType::class, [
                    'label' => 'Prénom',
                    'filter_emojis' => true,
                    'format_identity_case' => true,
                ])
                ->add('nickname', TextType::class, [
                    'label' => 'Pseudo',
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->add('nicknameUsed', null, [
                    'label' => 'Pseudo utilisé ?',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                ])
                ->add('birthdate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                    'required' => false,
                ])
                ->add('position', ActivityPositionType::class, [
                    'label' => 'Statut',
                ])
                ->add('mandates', ChoiceType::class, [
                    'label' => 'adherent.mandate.admin.label',
                    'choices' => Mandates::CHOICES,
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Notifications via e-mail et mobile :',
                    'choice_label' => 'label',
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('media', null, [
                    'label' => 'Photo',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Biographie',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
                ->add('twitterPageUrl', UrlType::class, [
                    'label' => 'Page Twitter',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://twitter.com/alexandredumoulin',
                    ],
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Page Facebook',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://facebook.com/alexandre-dumoulin',
                    ],
                ])
            ->end()
            ->with('Responsabilités locales', ['class' => 'col-md-6'])
                ->add('coordinatorCommitteeArea', CoordinatorManagedAreaType::class, [
                    'label' => 'coordinator.label.codes.committee',
                    'sector' => CoordinatorAreaSectors::COMMITTEE_SECTOR,
                ])
                ->add('managedArea', ReferentManagedAreaType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'coordinator.label.codes',
                    'required' => false,
                    'help' => "Laisser vide si l'adhérent n'est pas responsable procuration. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.",
                ])
                ->add('assessorManagedAreaCodesAsString', TextType::class, [
                    'label' => 'assessors_manager',
                    'required' => false,
                    'help' => "Laisser vide si l'adhérent n'est pas responsable assesseur. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.",
                ])
                ->add('coordinatorCitizenProjectArea', CoordinatorManagedAreaType::class, [
                    'label' => 'coordinator.label.codes.cp',
                    'sector' => CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR,
                ])
            ->end()
            ->with('Mandat électif', ['class' => 'col-md-6'])
                ->add('managedDistrict', 'sonata_type_model', [
                    'label' => 'Circonscription député',
                    'by_reference' => false,
                    'btn_add' => false,
                    'required' => false,
                ])
            ->end()
            ->with('Zone expérimentale 🚧', [
                'class' => 'col-md-6',
                'box_class' => 'box box-warning',
            ])
                ->add('canaryTester')
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventSubscriber(new ReferentManagedAreaListener())
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('nickname', null, [
                'label' => 'Pseudo',
                'show_filter' => false,
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.mb_strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', mb_strtolower($value['value']));

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags admin',
                'field_options' => [
                    'class' => AdherentTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('subscriptionTypes', ModelFilter::class, [
                'label' => 'Types de souscriptions',
                'field_options' => [
                    'class' => SubscriptionType::class,
                    'multiple' => true,
                    'choice_label' => 'label',
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('canaryTester')
            ->add('status', null, ['label' => 'Etat du compte'], 'choice', [
                'choices' => [
                    'Activé' => Adherent::ENABLED,
                    'Désactivé' => Adherent::DISABLED,
                ],
            ])
            ->add('referentTags', ModelAutocompleteFilter::class, [
                'label' => 'Tags référent souscrits',
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
            ->add('managedArea', ReferentTagAutocompleteFilter::class, [
                'label' => 'Tags référent gérés',
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.$field", 'managed_area')
                        ->leftJoin('managed_area.tags', 'tags')
                        ->andWhere('tags IN (:tags)')
                        ->setParameter('tags', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('role', CallbackFilter::class, [
                'label' => 'common.role',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => AdherentRoleEnum::toArray(),
                    'choice_label' => function (string $value) {
                        return $value;
                    },
                    'multiple' => true,
                ],
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    /** @var QueryBuilder $qb */

                    // Referent
                    if (\in_array(AdherentRoleEnum::REFERENT, $value['value'], true)) {
                        $where->add(sprintf('%s.managedArea IS NOT NULL', $alias));
                    }

                    // Committee supervisor & host
                    if ($committeeRoles = array_intersect([AdherentRoleEnum::COMMITTEE_SUPERVISOR, AdherentRoleEnum::COMMITTEE_HOST], $value['value'])) {
                        $qb->leftJoin(sprintf('%s.memberships', $alias), 'ms');
                        $where->add('ms.privilege IN (:committee_privileges)');
                        if (\in_array(AdherentRoleEnum::COMMITTEE_SUPERVISOR, $committeeRoles, true)) {
                            $privileges[] = CommitteeMembership::COMMITTEE_SUPERVISOR;
                        }

                        if (\in_array(AdherentRoleEnum::COMMITTEE_HOST, $committeeRoles, true)) {
                            $privileges[] = CommitteeMembership::COMMITTEE_HOST;
                        }
                        $qb->setParameter('committee_privileges', $privileges);
                    }

                    // Deputy
                    if (\in_array(AdherentRoleEnum::DEPUTY, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.managedDistrict', $alias), 'district');
                        $where->add('district IS NOT NULL');
                    }

                    // Board Member
                    if (\in_array(AdherentRoleEnum::BOARD_MEMBER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.boardMember', $alias), 'boardMember');
                        $where->add('boardMember IS NOT NULL');
                    }

                    // Coordinator
                    if (\in_array(AdherentRoleEnum::COORDINATOR, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.coordinatorCommitteeArea', $alias), 'coordinatorCommitteeArea');
                        $where->add('coordinatorCommitteeArea IS NOT NULL');
                    }

                    // REC
                    if (\in_array(AdherentRoleEnum::REC, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.coordinatorCitizenProjectArea', $alias), 'coordinatorCitizenProjectArea');
                        $where->add('coordinatorCitizenProjectArea IS NOT NULL');
                    }

                    // Procuration Manager
                    if (\in_array(AdherentRoleEnum::PROCURATION_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.procurationManagedArea', $alias), 'procurationManagedArea');
                        $where->add('procurationManagedArea IS NOT NULL AND procurationManagedArea.codes IS NOT NULL');
                    }

                    // Assessor Manager
                    if (\in_array(AdherentRoleEnum::ASSESSOR_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.assessorManagedArea', $alias), 'assessorManagedArea');
                        $where->add('assessorManagedArea IS NOT NULL AND assessorManagedArea.codes IS NOT NULL');
                    }

                    // User
                    if (\in_array(AdherentRoleEnum::USER, $value['value'], true)) {
                        $where->add(sprintf('%s.adherent = 0', $alias));
                    }

                    // Citizen project holder
                    if (\in_array(AdherentRoleEnum::CITIZEN_PROJECT_HOLDER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.citizenProjectMemberships', $alias), 'cpms');
                        $where->add('cpms.privilege = :cp_privilege');
                        $qb->setParameter('cp_privilege', CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);
                    }

                    if ($where->count()) {
                        $qb->andWhere($where);
                    }

                    return true;
                },
            ])
            ->add('mandates', CallbackFilter::class, [
                'label' => 'adherent.mandate.admin.label',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => Mandates::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $mandate) {
                        $where->add("$alias.mandates LIKE :mandate_".$mandate);
                        $qb->setParameter('mandate_'.$mandate, "%$mandate%");
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
        ;
    }

    /**
     * @param Adherent $subject
     */
    public function setSubject($subject)
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $subject;
        }

        parent::setSubject($subject);
    }

    public function preUpdate($object)
    {
        $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($this->beforeUpdate));
    }

    /**
     * @param Adherent $object
     */
    public function postUpdate($object)
    {
        // No need to handle referent tags update as they are not update-able from admin
        $this->emailSubscriptionHistoryManager->handleSubscriptionsUpdate($object, $subscriptionTypes = $this->beforeUpdate->getSubscriptionTypes());

        $this->dispatcher->dispatch(UserEvents::USER_UPDATE_SUBSCRIPTIONS, new UserEvent($object, null, null, $subscriptionTypes));
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($object));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('nickname', null, [
                'label' => 'Pseudo',
            ])
            ->add('nicknameUsed', null, [
                'label' => 'Pseudo utilisé ?',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
            ->add('tags', null, [
                'label' => 'Tags admin',
            ])
            ->add('referentTags', null, [
                'label' => 'Tags souscrits',
            ])
            ->add('managedAreaTags', null, [
                'label' => 'Tags gérés',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_managed_area_tags.html.twig',
            ])
            ->add('mandates', null, [
                'label' => 'adherent.mandate.admin.label',
                'template' => 'admin/adherent/list_mandates.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'UUID' => 'uuid',
            'Email' => 'emailAddress',
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Date de naissance' => 'birthdate',
            'Téléphone' => 'phone',
            'Inscrit(e) le' => 'registeredAt',
            'Sexe' => 'gender',
            'Addresse' => 'postAddress.address',
            'Code postal' => 'postAddress.postalCode',
            'Ville' => 'postAddress.cityName',
            'Pays' => 'postAddress.country',
        ];
    }
}
