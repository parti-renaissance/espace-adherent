<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\BoardMember\Role;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\Admin\CoordinatorManagedAreaType;
use AppBundle\Form\EventListener\BoardMemberListener;
use AppBundle\Form\EventListener\CoordinatorManagedAreaListener;
use AppBundle\Form\GenderType;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

    public function __construct($code, $class, $baseControllerName, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
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
                ->add('managedAreaCodesAsString', null, [
                    'label' => 'coordinator.label.codes',
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
                ->add('emailsSubscriptions', 'array', [
                    'label' => 'Abonné aux mails',
                ])
                ->add('hasSubscribedLocalHostEmails', 'boolean', [
                    'label' => 'Abonné aux mails de comités ?',
                ])
                ->add('comMobile', 'boolean', [
                    'label' => 'Accepte de recevoir des SMS de la part d\'En Marche !',
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
                ->add('emailsSubscriptions', ChoiceType::class, [
                    'choices' => AdherentEmailSubscription::SUBSCRIPTIONS,
                    'label' => 'Abonné aux mails :',
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('hasSubscribedLocalHostEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails de comités ?',
                    'required' => false,
                ])
            ->end()
            ->with('Référent', ['class' => 'col-md-6'])
                ->add('managedArea.codesAsString', TextType::class, [
                    'label' => 'coordinator.label.codes',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas référent. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
                ->add('managedArea.markerLatitude', TextType::class, [
                    'label' => 'Latitude du point sur la carte des référents',
                    'required' => false,
                ])
                ->add('managedArea.markerLongitude', TextType::class, [
                    'label' => 'Longitude du point sur la carte des référents',
                    'required' => false,
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-3'])
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
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'coordinator.label.codes',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas responsable procuration. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
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
            ->with('Tags', ['class' => 'col-md-6'])
                ->add('tags', 'sonata_type_model', [
                    'multiple' => true,
                    'by_reference' => false,
                    'btn_add' => false,
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventSubscriber(new BoardMemberListener())
            ->addEventSubscriber(new CoordinatorManagedAreaListener())
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
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

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
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'label' => 'N\'afficher que les référents',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.managedArea.codes', $alias).' IS NOT NULL');

                    return true;
                },
            ])
            ->add('supervisor', CallbackFilter::class, [
                'label' => 'N\'afficher que les animateurs',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->leftJoin(sprintf('%s.memberships', $alias), 'ms');
                    $qb->andWhere(sprintf('ms.privilege', $alias).' = :privilege');
                    $qb->setParameter('privilege', CommitteeMembership::COMMITTEE_SUPERVISOR);

                    return true;
                },
            ])
            ->add('host', CallbackFilter::class, [
                'label' => 'N\'afficher que les co-animateurs',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->leftJoin(sprintf('%s.memberships', $alias), 'mss');
                    $qb->andWhere(sprintf('mss.privilege', $alias).' = :privilege');
                    $qb->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST);

                    return true;
                },
            ])

            ->add('tags', CallbackFilter::class, [
                'label' => 'Tags',
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $value = array_map('trim', explode(',', strtolower($value['value'])));
                    $qb->leftJoin(sprintf('%s.tags', $alias), 't');
                    $qb->andWhere($qb->expr()->in('LOWER(t.name)', $value));

                    return true;
                },
            ])
        ;
    }

    /**
     * @param Adherent $object
     */
    public function postUpdate($object)
    {
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
            ->add('emailsSubscriptions', 'array', [
                'label' => 'Accepte les e-mails',
                'inline' => false,
            ])
            ->add('comMobile', 'boolean', [
                'label' => 'Accepte les SMS',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }
}
