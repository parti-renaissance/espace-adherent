<?php

namespace AppBundle\Admin;

use AppBundle\Adherent\AdherentRoleEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\BoardMember\Role;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\Admin\CoordinatorManagedAreaType;
use AppBundle\Form\Admin\ReferentManagedAreaType;
use AppBundle\Form\EventListener\BoardMemberListener;
use AppBundle\Form\EventListener\CoordinatorManagedAreaListener;
use AppBundle\Form\EventListener\ReferentManagedAreaListener;
use AppBundle\Form\GenderType;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'registeredAt',
    ];

    private $dispatcher;
    private $emailSubscriptionHistoryManager;

    /**
     * @var array Useful on update to know state before update
     */
    private $oldEmailsSubscriptions;

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

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/adherent/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/adherent/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                    'filter_emojis' => true,
                ])
                ->add('firstName', TextType::class, [
                    'label' => 'Prénom',
                    'filter_emojis' => true,
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
            ->end()
            ->with('Référent', ['class' => 'col-md-3'])
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                ])
                ->add('managedArea.tags', null, [
                    'label' => 'referent.label.tags',
                ])
                ->add('managedAreaMarkerLatitude', null, [
                    'label' => 'Latitude du point sur la carte',
                ])
                ->add('managedAreaMarkerLongitude', null, [
                    'label' => 'Longitude du point sur la carte',
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-3'])
                ->add('isCoordinator', 'boolean', [
                    'label' => 'Est coordinateur ?',
                ])
                ->add('coordinatorManagedAreaCodesAsString', null, [
                    'label' => 'coordinator.label.codes',
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('isProcurationManager', 'boolean', [
                    'label' => 'Est responsable procuration ?',
                ])
                ->add('procurationManagedAreaCodesAsString', null, [
                    'label' => 'coordinator.label.codes',
                ])
            ->end()
            ->with('Compte', ['class' => 'col-md-6'])
                ->add('status', null, [
                    'label' => 'Etat du compte',
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Abonné aux notifications via e-mail et mobile',
                    'associated_property' => 'label',
                ])
            ->end()
            ->with('Membre du Conseil', ['class' => 'col-md-6'])
                ->add('isBoardMember', 'boolean', [
                    'label' => 'Est membre du Conseil ?',
                ])
                ->add('boardMember.area', null, [
                    'label' => 'Région',
                ])
                ->add('boardMember.roles', null, [
                    'label' => 'Rôles',
                    'template' => 'admin/adherent/list_board_member_roles.html.twig',
                ])
            ->end()
            ->with('Député(e)', ['class' => 'col-md-6'])
                ->add('isDeputy', 'boolean', [
                    'label' => 'Est un(e) député(e) ?',
                ])
                ->add('managedDistrict.name', null, [
                    'label' => 'Nom de la circonscription du député',
                ])
            ->end()
            ->with('Tags', ['class' => 'col-md-6'])
                ->add('tags', null, [
                    'label' => 'Tags de l\'adhérent',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                    'format_identity_case' => true,
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                    'format_identity_case' => true,
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
            ->end()
            ->with('Compte', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Abonné aux mails :',
                    'choice_label' => 'label',
                    'required' => false,
                    'multiple' => true,
                ])
            ->end()
            ->with('Référent', ['class' => 'col-md-6'])
                ->add('managedArea', ReferentManagedAreaType::class, [
                    'label' => false,
                    'required' => false,
                ])
            ->end()
            ->with('Tags', ['class' => 'col-md-6'])
                ->add('tags', 'sonata_type_model', [
                    'multiple' => true,
                    'by_reference' => false,
                    'btn_add' => false,
                ])
            ->end()
            ->with('Membre du Conseil', ['class' => 'col-md-6'])
                ->add('boardMemberArea', ChoiceType::class, [
                    'label' => 'Région',
                    'choices' => BoardMember::AREAS_CHOICES,
                    'required' => false,
                    'mapped' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas membre du Conseil.',
                ])
                ->add('boardMemberRoles', 'sonata_type_model', [
                    'expanded' => true,
                    'multiple' => true,
                    'btn_add' => false,
                    'class' => Role::class,
                    'mapped' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas membre du Conseil.',
                ])
            ->end()
            ->with('Circonscription du député', ['class' => 'col-md-6'])
                ->add('managedDistrict', 'sonata_type_model', [
                    'label' => 'Nom de la circonscription du député',
                    'by_reference' => false,
                    'btn_add' => false,
                    'required' => false,
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-6'])
                ->add('coordinatorManagedAreas', CollectionType::class, [
                    'label' => false,
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas coordinateur. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                    'entry_type' => CoordinatorManagedAreaType::class,
                    'allow_add' => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-6'])
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'coordinator.label.codes',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas responsable procuration. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventSubscriber(new BoardMemberListener())
            ->addEventSubscriber(new CoordinatorManagedAreaListener())
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
            ->add('tags', CallbackFilter::class, [
                'label' => 'Tags adhérent',
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $value = array_map('trim', explode(',', mb_strtolower($value['value'])));
                    $qb->leftJoin(sprintf('%s.tags', $alias), 't');
                    $qb->andWhere($qb->expr()->in('LOWER(t.name)', $value));

                    return true;
                },
            ])
            ->add('referentTags', CallbackFilter::class, [
                'label' => 'Tags référent souscrits',
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $value = array_map('trim', explode(',', mb_strtolower($value['value'])));
                    $qb->leftJoin("$alias.referentTags", 'referent_tag');
                    $qb->andWhere($qb->expr()->in('LOWER(referent_tag.name)', $value));

                    return true;
                },
            ])
            ->add('managedAreaTags', CallbackFilter::class, [
                'label' => 'Tags référent gérés',
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $value = array_map('trim', explode(',', mb_strtolower($value['value'])));
                    $qb->leftJoin("$alias.managedArea", 'managed_area');
                    $qb->leftJoin('managed_area.tags', 'managed_area_tag');
                    $qb->andWhere($qb->expr()->in('LOWER(managed_area_tag.name)', $value));

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
                        $qb->leftJoin(sprintf('%s.coordinatorManagedAreas', $alias), 'coordinatorManagedArea');
                        $where->add('coordinatorManagedArea IS NOT NULL');
                    }

                    // Procuration Manager
                    if (\in_array(AdherentRoleEnum::PROCURATION_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.procurationManagedArea', $alias), 'procurationManagedArea');
                        $where->add('procurationManagedArea IS NOT NULL AND procurationManagedArea.codes IS NOT NULL');
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
        if (null === $this->oldEmailsSubscriptions) {
            $this->oldEmailsSubscriptions = $subject->getSubscriptionTypes();
        }
        parent::setSubject($subject);
    }

    /**
     * @param Adherent $object
     */
    public function postUpdate($object)
    {
        // No need to handle referent tags update as they are not update-able from admin
        $this->emailSubscriptionHistoryManager->handleSubscriptionsUpdate($object, $this->oldEmailsSubscriptions);

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
                'label' => 'Tags adhérent',
            ])
            ->add('referentTags', null, [
                'label' => 'Tags souscrits',
            ])
            ->add('managedAreaTags', null, [
                'label' => 'Tags gérés',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_managed_area_tags.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }
}
