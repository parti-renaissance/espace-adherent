<?php

namespace App\Admin;

use App\Address\AddressInterface;
use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\AdherentProfile\AdherentProfileHandler;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\AdherentRoleFilter;
use App\Admin\Filter\AdherentTagFilter;
use App\Admin\Filter\PostalCodeFilter;
use App\Admin\Filter\StaticAdherentTagFilter;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Committee\CommitteeMembershipManager;
use App\Contribution\ContributionStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentStaticLabel;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\SubscriptionType;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Form\ActivityPositionType;
use App\Form\AdherentMandateType;
use App\Form\Admin\AdherentZoneBasedRoleType;
use App\Form\Admin\ElectedRepresentativeAdherentMandateType;
use App\Form\Admin\JecouteManagedAreaType;
use App\Form\EventListener\CommitteeMembershipListener;
use App\Form\EventListener\RevokeManagedAreaSubscriber;
use App\Form\GenderType;
use App\Form\ReCountryType;
use App\Form\TelNumberType;
use App\FranceCities\FranceCities;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\History\EmailSubscriptionHistoryHandler;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdherentAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected EventDispatcherInterface $dispatcher;
    protected EmailSubscriptionHistoryHandler $emailSubscriptionHistoryManager;
    protected AdherentProfileHandler $adherentProfileHandler;
    protected LoggerInterface $logger;
    protected FranceCities $franceCities;
    private TranslatorInterface $translator;
    private TagTranslator $tagTranslator;
    private CommitteeMembershipManager $committeeMembershipManager;
    private Security $security;

    /**
     * State of adherent data before update
     *
     * @var Adherent
     */
    protected $beforeUpdate;

    /**
     * State of adhernet elected representative mandates before update
     *
     * @var Collection
     */
    protected $electedMandatesBeforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryManager,
        AdherentProfileHandler $adherentProfileHandler,
        LoggerInterface $logger,
        FranceCities $franceCities,
        TagTranslator $tagTranslator,
        TranslatorInterface $translator,
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->emailSubscriptionHistoryManager = $emailSubscriptionHistoryManager;
        $this->adherentProfileHandler = $adherentProfileHandler;
        $this->logger = $logger;
        $this->franceCities = $franceCities;
        $this->tagTranslator = $tagTranslator;
        $this->translator = $translator;

        $this->electedMandatesBeforeUpdate = new ArrayCollection();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::PER_PAGE] = 32;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('gender', null, [
                    'label' => 'Civilité',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse email',
                ])
                ->add('nationality', null, [
                    'label' => 'Nationalité',
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
                ->add('utmSource', null, ['label' => 'UTM Source'])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne'])
            ->end()
            ->with('Adresse', ['class' => 'col-md-3'])
                ->add('postAddress.address', null, ['label' => 'Rue'])
                ->add('postAddress.additionalAddress', null, ['label' => 'Complément d\'adresse'])
                ->add('postAddress.postalCode', null, ['label' => 'Code postal'])
                ->add('postAddress.cityName', null, ['label' => 'Ville'])
                ->add('postAddress.country', null, ['label' => 'Pays'])
                ->add('postAddress.latitude', null, ['label' => 'Latitude'])
                ->add('postAddress.longitude', null, ['label' => 'Longitude'])
            ->end()
            ->with('Information de compte', ['class' => 'col-md-3'])
                ->add('status', 'trans', [
                    'label' => 'État du compte',
                ])
                ->add('registeredAt', null, [
                    'label' => 'Créé le',
                ])
                ->add('activatedAt', null, [
                    'label' => 'Activé le',
                ])
                ->add('lastLoggedAt', null, [
                    'label' => 'Dernière connexion',
                ])
                ->add('certifiedAt', null, [
                    'label' => 'Certifié le',
                ])
                ->add('lastMembershipDonation', null, [
                    'label' => 'Statut de la cotisation',
                    'template' => 'admin/adherent/show_last_membership_donation_date.html.twig',
                ])
            ->end()
            ->with('Abonnement', ['class' => 'col-md-6'])
                ->add('subscriptionTypes', null, [
                    'label' => 'Abonné aux notifications via email et mobile',
                    'associated_property' => 'label',
                ])
            ->end()
            ->with('Responsabilités locales', ['class' => 'col-md-6'])
                ->add('type', null, [
                    'label' => 'Rôles',
                    'template' => 'admin/adherent/show_statuses.html.twig',
                ])
            ->end()
            ->with('Responsabilités politiques', ['class' => 'col-md-6'])
                ->add('electedRepresentative', null, [
                    'label' => 'Identité de l\'élu',
                    'template' => 'admin/adherent/show_elected_representative.html.twig',
                    'virtual_field' => true,
                ])
                ->add('er_adherent_mandates', null, [
                    'label' => 'Mandats',
                    'template' => 'admin/adherent/show_er_adherent_mandates.html.twig',
                    'virtual_field' => true,
                ])
                ->add('contribution', null, [
                    'label' => 'Cotisation',
                    'template' => 'admin/adherent/show_contribution.html.twig',
                    'virtual_field' => true,
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if ($this->isGrantedAdherentAdminRole()) {
            $form
                ->tab('Informations générales')
                    ->with('Informations personnelles', ['class' => 'col-md-6'])
                        ->add('gender', GenderType::class, [
                            'label' => 'Civilité',
                        ])
                        ->add('lastName', TextType::class, [
                            'label' => 'Nom',
                            'format_identity_case' => true,
                        ])
                        ->add('firstName', TextType::class, [
                            'label' => 'Prénom',
                            'format_identity_case' => true,
                        ])
                        ->add('emailAddress', null, [
                            'label' => 'Adresse email',
                        ])
                        ->add('nationality', ReCountryType::class, [
                            'label' => 'Nationalité',
                        ])
                        ->add('phone', TelNumberType::class, [
                            'required' => false,
                        ])
                        ->add('birthdate', DatePickerType::class, [
                            'label' => 'Date de naissance',
                            'required' => false,
                        ])
                        ->add('position', ActivityPositionType::class, [
                            'label' => 'Statut',
                        ])
                    ->end()
                    ->with('Labels statiques', ['class' => 'col-md-6'])
                        ->add('staticLabels', null, [
                            'label' => false,
                            'required' => false,
                            'choice_label' => static function (AdherentStaticLabel $staticLabel): string {
                                return $staticLabel->label;
                            },
                            'group_by' => static function (AdherentStaticLabel $staticLabel): string {
                                return $staticLabel->category->label;
                            },
                        ])
                    ->end()
                    ->with('Comité local', ['class' => 'col-md-6'])
                        ->add('committee', ModelType::class, [
                            'label' => 'Comité',
                            'class' => Committee::class,
                            'required' => false,
                            'mapped' => false,
                        ])
                    ->end()
                    ->with('Adresse', ['class' => 'col-md-6'])
                        ->add('postAddress.address', TextType::class, ['label' => 'Rue'])
                        ->add('postAddress.additionalAddress', TextType::class, ['label' => 'Complément d\'adresse', 'required' => false])
                        ->add('postAddress.postalCode', TextType::class, ['label' => 'Code postal'])
                        ->add('postAddress.cityName', TextType::class, ['label' => 'Ville'])
                        ->add('postAddress.country', ReCountryType::class, ['label' => 'Pays'])
                        ->add('postAddress.latitude', NumberType::class, [
                            'label' => 'Latitude',
                            'html5' => true,
                            'scale' => 6,
                        ])
                        ->add('postAddress.longitude', NumberType::class, [
                            'label' => 'Longitude',
                            'html5' => true,
                            'scale' => 6,
                        ])
                    ->end()
                    ->with('Information de compte', ['class' => 'col-md-6'])
                        ->add('status', ChoiceType::class, [
                            'label' => 'État du compte',
                            'choices' => [
                                'En attente' => Adherent::PENDING,
                                'Activé' => Adherent::ENABLED,
                                'Désactivé' => Adherent::DISABLED,
                            ],
                        ])
                        ->add('lastMembershipDonationDate', HiddenType::class, [
                            'label' => false,
                            'required' => false,
                            'mapped' => false,
                        ])
                        ->add('tags', HiddenType::class, [
                            'label' => 'Labels',
                            'required' => false,
                            'mapped' => false,
                        ])
                    ->end()
                    ->with('Abonnement', ['class' => 'col-md-6'])
                        ->add('subscriptionTypes', null, [
                            'label' => 'Notifications via email et mobile :',
                            'choice_label' => 'label',
                            'required' => false,
                            'multiple' => true,
                        ])
                    ->end()
                    ->with('Réseaux Sociaux', ['class' => 'col-md-6'])
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
                        ->add('linkedinPageUrl', UrlType::class, [
                            'label' => 'Page LinkedIn',
                            'required' => false,
                            'attr' => [
                                'placeholder' => 'https://linkedin.com/in/alexandre-dumoulin',
                            ],
                        ])
                        ->add('telegramPageUrl', UrlType::class, [
                            'label' => 'Telegram',
                            'required' => false,
                            'attr' => [
                                'placeholder' => 'https://t.me/alexandre-dumoulin',
                            ],
                        ])
                    ->end()
                    ->with('Zone expérimentale 🚧', [
                        'class' => 'col-md-6',
                        'box_class' => 'box box-warning',
                    ])
                        ->add('canaryTester')
                        ->add('sandboxMode')
                    ->end()
                ->end()
                ->tab('Responsabilités internes')
                    ->with('Rôles', ['class' => 'col-md-6'])
                        ->add('zoneBasedRoles', CollectionType::class, [
                            'error_bubbling' => false,
                            'required' => false,
                            'label' => false,
                            'entry_type' => AdherentZoneBasedRoleType::class,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'entry_options' => [
                                'model_manager' => $this->getModelManager(),
                            ],
                            'by_reference' => false,
                        ])
                    ->end()
                    ->with('Responsabilités locales', ['class' => 'col-md-6'])
                        ->add('jecouteManagedArea', JecouteManagedAreaType::class, [
                            'label' => 'jecoute_manager',
                            'required' => false,
                            'help' => "Laisser vide si l'adhérent n'est pas responsable des questionnaires. Choisissez un département, un arrondissement de Paris ou une circonscription des Français établis hors de France",
                            'model_manager' => $this->getModelManager(),
                        ])
                        ->add('nationalRole', null, [
                            'label' => 'Rôle National',
                            'required' => false,
                        ])
                        ->add('nationalCommunicationRole', null, [
                            'label' => 'Rôle National communication',
                            'required' => false,
                        ])
                    ->end()
                    ->with('Responsable d\'appel', ['class' => 'col-md-6'])
                        ->add('phoningManagerRole', null, [
                            'label' => 'Rôle Responsable phoning',
                            'required' => false,
                        ])
                    ->end()
                    ->with('Porte-à-porte', ['class' => 'col-md-6'])
                        ->add('papNationalManagerRole', null, [
                            'label' => 'Responsable National PAP',
                            'required' => false,
                        ])
                        ->add('papUserRole', null, [
                            'label' => 'Utilisateur PAP app mobile',
                            'required' => false,
                        ])
                    ->end()
                ->end()
            ;
        }

        $form
            ->tab('Responsabilités politiques')
                ->with('Mandats', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-warning',
                ])
                    ->add('mandates', AdherentMandateType::class, [
                        'label' => 'Mandat(s) déclaré(s)',
                        'required' => false,
                        'multiple' => true,
                    ])
                    ->add('electedRepresentativeMandates', CollectionType::class, [
                        'error_bubbling' => false,
                        'required' => false,
                        'label' => 'Mandat(s) actif(s)',
                        'entry_type' => ElectedRepresentativeAdherentMandateType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'entry_options' => [
                            'model_manager' => $this->getModelManager(),
                        ],
                        'by_reference' => true,
                    ])
                ->end()
                ->with('Cotisation', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-info',
                ])
                    ->add('contributionStatus', TextType::class, [
                        'label' => false,
                        'required' => false,
                        'mapped' => false,
                    ])
                ->end()
            ->end()
            ->tab('Dons / Cotisations')
                ->with('', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-info',
                ])
                    ->add('forcedMembership', null, [
                        'label' => 'Toujours à jour de cotisation',
                        'required' => false,
                        'disabled' => !$this->isGranted('ROLE_SUPER_ADMIN'),
                    ])
                    ->add('donations', HiddenType::class, [
                        'label' => false,
                        'required' => false,
                        'mapped' => false,
                    ])
                ->end()
            ->end()
        ;

        if ($this->isGrantedAdherentAdminRole()) {
            $form->getFormBuilder()
                ->addEventSubscriber(new CommitteeMembershipListener($this->committeeMembershipManager))
                ->addEventSubscriber(new RevokeManagedAreaSubscriber())
                ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                    /** @var Adherent $adherent */
                    $adherent = $event->getData();
                    $address = $adherent->getPostAddress();

                    if (AddressInterface::FRANCE === $address->getCountry() && $address->getCityName() && $address->getPostalCode()) {
                        $city = $this->franceCities->getCityByPostalCodeAndName($address->getPostalCode(), $address->getCityName());

                        if ($city) {
                            $address->setCity(\sprintf('%s-%s', $address->getPostalCode(), $city->getInseeCode()));
                        }
                    }
                })
            ;
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('status', ChoiceFilter::class, [
                'label' => 'État du compte',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'En attente' => Adherent::PENDING,
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ],
            ])
            ->add('hasProfileImage', CallbackFilter::class, [
                'label' => 'Avec avatar',
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(\sprintf('%s.imageName IS %s', $alias, 1 === $value->getValue() ? 'NOT NULL' : 'NULL'));

                    return true;
                },
            ])
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.emailAddress", "$alias.emailAddress"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                            "$alias.publicId",
                        ]
                    );

                    return true;
                },
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse email',
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
            ->add('tags_adherents', AdherentTagFilter::class, [
                'label' => 'Labels adhérents',
                'show_filter' => true,
                'tags' => TagEnum::getAdherentTags(),
            ])
            ->add('tags_elected', AdherentTagFilter::class, [
                'label' => 'Labels élus',
                'show_filter' => true,
                'tags' => TagEnum::getElectTags(),
            ])
            ->add('tags_static', StaticAdherentTagFilter::class, [
                'label' => 'Labels divers',
                'show_filter' => true,
            ])
            ->add('staticLabels', null, [
                'label' => 'Labels statiques',
                'show_filter' => true,
                'field_options' => [
                    'multiple' => true,
                    'choice_label' => static function (AdherentStaticLabel $staticLabel): string {
                        return $staticLabel->label;
                    },
                    'group_by' => static function (AdherentStaticLabel $staticLabel): string {
                        return $staticLabel->category->label;
                    },
                ],
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
                'show_filter' => true,
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
            ->add('postalCode', PostalCodeFilter::class, [
                'label' => 'Code postal',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
                'field_type' => ReCountryType::class,
            ])
            ->add('mailchimpStatus', ChoiceFilter::class, [
                'label' => 'Abonnement email',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ContactStatusEnum::values(),
                    'choice_label' => function (string $label) {
                        return 'mailchimp_contact.status.'.$label;
                    },
                ],
            ])
            ->add('role', AdherentRoleFilter::class, [
                'label' => 'common.role',
                'show_filter' => true,
            ])
            ->add('er_adherent_mandate_type', CallbackFilter::class, [
                'label' => 'Mandat(s) élu',
                'show_filter' => true,
                'field_type' => AdherentMandateType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin(
                            ElectedRepresentativeAdherentMandate::class,
                            'eam',
                            Expr\Join::WITH,
                            \sprintf('%s.id = eam.adherent', $alias)
                        )
                        ->andWhere('eam.finishAt IS NULL')
                        ->andWhere('eam.mandateType IN (:er_adherent_mandate_types)')
                        ->setParameter('er_adherent_mandate_types', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) déclaré(s)',
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
                        $where->add("FIND_IN_SET(:mandate_$mandate, $alias.mandates) > 0");
                        $qb->setParameter("mandate_$mandate", $mandate);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('er_adherent_mandate_ongoing', CallbackFilter::class, [
                'label' => 'Mandat en cours ?',
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb
                                ->innerJoin(
                                    ElectedRepresentativeAdherentMandate::class,
                                    'er_adherent_mandate_ongoing',
                                    Expr\Join::WITH,
                                    \sprintf('%s.id = er_adherent_mandate_ongoing.adherent', $alias)
                                )
                                ->andWhere('er_adherent_mandate_ongoing.finishAt IS NULL')
                            ;

                            break;
                        case BooleanType::TYPE_NO:
                            $qb
                                ->innerJoin(
                                    ElectedRepresentativeAdherentMandate::class,
                                    'er_adherent_mandate_ongoing',
                                    Expr\Join::WITH,
                                    \sprintf('%s.id = er_adherent_mandate_ongoing.adherent', $alias)
                                )
                                ->andWhere('er_adherent_mandate_ongoing.finishAt IS NOT NULL')
                            ;

                            break;
                    }

                    return true;
                },
            ])
            ->add('revenueDeclared', CallbackFilter::class, [
                'label' => 'Revenus élu déclarés ?',
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

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
                'label' => 'Éligible à la cotisation élu ?',
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

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
            ->add('ageMin', CallbackFilter::class, [
                'label' => 'Âge minimum',
                'field_type' => IntegerType::class,
                'field_options' => [
                    'attr' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $ageMin = $value->getValue();
                    $now = new \DateTimeImmutable();

                    $qb
                        ->andWhere("$alias.birthdate <= :min_age_birth_date")
                        ->setParameter('min_age_birth_date', $now->sub(new \DateInterval(\sprintf('P%dY', $ageMin))))
                    ;

                    return true;
                },
            ])
            ->add('ageMax', CallbackFilter::class, [
                'label' => 'Âge maximum',
                'field_type' => IntegerType::class,
                'field_options' => [
                    'attr' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $ageMax = $value->getValue();
                    $now = new \DateTimeImmutable();

                    $qb
                        ->andWhere("$alias.birthdate >= :max_age_birth_date")
                        ->setParameter('max_age_birth_date', $now->sub(new \DateInterval(\sprintf('P%dY', $ageMax))))
                    ;

                    return true;
                },
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date de création de compte',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('firstMembershipDonation', DateRangeFilter::class, [
                'label' => 'Date de première cotisation',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('lastMembershipDonation', DateRangeFilter::class, [
                'label' => 'Date de dernière cotisation',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('lastLoggedAt', DateRangeFilter::class, [
                'label' => 'Date de dernière connexion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('certified', CallbackFilter::class, [
                'label' => 'Certifié',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return "global.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere("$alias.certifiedAt IS NOT NULL");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.certifiedAt IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('certifiedAt', DateRangeFilter::class, [
                'label' => 'Date de certification',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('canaryTester', null, [
                'label' => 'Testeur Canary',
            ])
            ->add('adherent_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) internes',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge(
                        TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS,
                        ['TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT]
                    ),
                    'choice_label' => function (string $choice) {
                        if ('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $choice) {
                            return 'territorial_council.membership.quality.elected_candidate_adherent';
                        }

                        return 'political_committee.membership.quality.'.$choice;
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $mandatesCondition = 'adherentMandate.quality IN (:qualities)';
                    if (\in_array('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, $value->getValue())) {
                        $mandatesCondition = '(adherentMandate.quality IN (:qualities) OR adherentMandate.committee IS NOT NULL)';
                    }

                    $qb
                        ->leftJoin("$alias.adherentMandates", 'adherentMandate')
                        ->andWhere('adherentMandate.finishAt IS NULL')
                        ->andWhere($mandatesCondition)
                        ->setParameter('qualities', $value->getValue())
                    ;

                    return true;
                },
            ])
        ;
    }

    /**
     * @param Adherent $object
     */
    protected function alterObject(object $object): void
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $object;
            $this->electedMandatesBeforeUpdate = new ArrayCollection($object->getElectedRepresentativeMandates());

            $this->dispatcher->dispatch(
                new AdministratorActionEvent($this->getAdministrator(), $object),
                AdministratorActionEvents::ADMIN_USER_PROFILE_BEFORE_UPDATE
            );
        }
    }

    protected function preUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new UserEvent($this->beforeUpdate), UserEvents::USER_BEFORE_UPDATE);
    }

    /**
     * @param Adherent $object
     */
    protected function postUpdate(object $object): void
    {
        foreach ($object->getElectedRepresentativeMandates() as $mandate) {
            if (
                \in_array($mandate->mandateType, [
                    MandateTypeEnum::DEPUTE,
                    MandateTypeEnum::DEPUTE_EUROPEEN,
                    MandateTypeEnum::SENATEUR,
                    MandateTypeEnum::MINISTER,
                ])
                && !$mandate->isEnded()
                && !$this->electedMandatesBeforeUpdate->contains($mandate)
            ) {
                $object->setContributionStatus(ContributionStatusEnum::ELIGIBLE);
                $object->addRevenueDeclaration(10000);

                break;
            }
        }

        $this->adherentProfileHandler->updateReferentTagsAndSubscriptionHistoryIfNeeded($object);

        $this->emailSubscriptionHistoryManager->handleSubscriptionsUpdate($object, $this->beforeUpdate->getSubscriptionTypes());

        $this->dispatcher->dispatch($event = new UserEvent($object), UserEvents::USER_UPDATED);
        $this->dispatcher->dispatch($event, UserEvents::USER_UPDATED_IN_ADMIN);

        $this->dispatcher->dispatch(
            new AdministratorActionEvent($this->getAdministrator(), $object),
            AdministratorActionEvents::ADMIN_USER_PROFILE_AFTER_UPDATE
        );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('publicId', null, ['label' => 'PID'])
            ->add('lastName', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date de création de compte',
            ])
            ->add('firstMembershipDonation', null, [
                'label' => 'Première cotisation',
            ])
            ->add('lastMembershipDonation', null, [
                'label' => 'Dernière cotisation',
            ])
            ->add('lastLoggedAt', null, [
                'label' => 'Dernière connexion',
            ])
            ->add('type', null, [
                'label' => 'Compte',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
            ->add('allMandates', null, [
                'label' => 'Mandats',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_mandates.html.twig',
            ])
            ->add('mailchimpStatus', null, [
                'label' => 'Abonnement',
                'template' => 'admin/adherent/list_email_subscription_status.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => function (array $adherent) {
            /** @var Adherent $adherent */
            $adherent = $adherent[0];

            try {
                return [
                    'PID' => $adherent->getPublicId(),
                    'Email' => $adherent->getEmailAddress(),
                    'Civilité' => $this->translator->trans(array_search($adherent->getGender(), Genders::CIVILITY_CHOICES, true)),
                    'Prénom' => $adherent->getFirstName(),
                    'Nom' => $adherent->getLastName(),
                    'Date de naissance' => $adherent->getBirthdate()?->format('d/m/Y'),
                    'Téléphone' => PhoneNumberUtils::format($adherent->getPhone()),
                    'Adresse postale' => $adherent->getAddress(),
                    'Complément d\'adresse' => $adherent->getAdditionalAddress(),
                    'Code postal' => $adherent->getPostalCode(),
                    'Code INSEE' => $adherent->getInseeCode(),
                    'Ville' => $adherent->getCityName(),
                    'Pays' => Countries::getName($adherent->getCountry()),
                    'Comité' => (string) $adherent->getCommitteeMembership()?->getCommittee(),
                    'Circonscription' => implode(', ', array_map(function (Zone $zone): string {
                        return $zone->getCode().' - '.$zone->getName();
                    }, $adherent->getZonesOfType(Zone::DISTRICT))),
                    'Département' => implode(', ', array_map(function (Zone $zone): string {
                        return $zone->getCode().' - '.$zone->getName();
                    }, $adherent->getParentZonesOfType(Zone::DEPARTMENT))),
                    'Région' => implode(', ', array_map(function (Zone $zone): string {
                        return $zone->getCode().' - '.$zone->getName();
                    }, $adherent->getParentZonesOfType(Zone::REGION))),
                    'Rôles' => implode(', ', array_map(function (AdherentZoneBasedRole $role): string {
                        return \sprintf(
                            '%s [%s]',
                            $this->translator->trans('role.'.$role->getType()),
                            implode(', ', array_map(function (Zone $zone): string {
                                return \sprintf(
                                    '%s (%s)',
                                    $zone->getName(),
                                    $zone->getCode()
                                );
                            }, $role->getZones()->toArray()))
                        );
                    }, $adherent->getZoneBasedRoles())),
                    'Labels militants' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (!\in_array($tag, TagEnum::getAdherentTags(), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent->tags))),
                    'Labels Élus' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (!\in_array($tag, TagEnum::getElectTags(), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent->tags))),
                    'Déclaration de mandats' => implode(', ', array_map(function (string $declaredMandate): string {
                        return $this->translator->trans("adherent.mandate.type.$declaredMandate");
                    }, $adherent->getMandates())),
                    'Mandats' => implode(', ', array_map(function (ElectedRepresentativeAdherentMandate $mandate): string {
                        $str = $this->translator->trans('adherent.mandate.type.'.$mandate->mandateType);

                        if ($zone = $mandate->zone) {
                            $str .= \sprintf(
                                ' [%s (%s)]',
                                $zone->getName(),
                                $zone->getCode()
                            );
                        }

                        return $str;
                    }, $adherent->getElectedRepresentativeMandates())),
                    'Labels divers' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (\in_array($tag, array_merge(TagEnum::getAdherentTags(), TagEnum::getElectTags()), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent->tags))),
                    'Labels statiques' => implode(', ', array_map(function (AdherentStaticLabel $label): string {
                        return $label->label;
                    }, $adherent->getStaticLabels()->toArray())),
                    'Préférences de notifications' => implode(', ', array_map(function (SubscriptionType $subscriptionType): string {
                        return $subscriptionType->getLabel();
                    }, $adherent->getSubscriptionTypes())),
                    'Date de création de compte' => $adherent->getRegisteredAt()?->format('d/m/Y H:i:s'),
                    'Date de première cotisation' => $adherent->getFirstMembershipDonation()?->format('d/m/Y H:i:s'),
                    'Date de dernière cotisation' => $adherent->getLastMembershipDonation()?->format('d/m/Y H:i:s'),
                    'Date de dernière connexion' => $adherent->getLastLoggedAt()?->format('d/m/Y H:i:s'),
                ];
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error exporting Adherent with UUID: %s. (%s)', $adherent->getUuid(), $e->getMessage()),
                    ['exception' => $e]
                );

                return [
                    'ID' => $adherent->getId(),
                    'UUID' => $adherent->getUuid()->toString(),
                    'Email' => $adherent->getEmailAddress(),
                ];
            }
        }];
    }

    #[Required]
    public function setCommitteeMembershipManager(CommitteeMembershipManager $committeeMembershipManager): void
    {
        $this->committeeMembershipManager = $committeeMembershipManager;
    }

    protected function isGrantedAdherentAdminRole(): bool
    {
        return $this->isGranted('ROLE_ADMIN_ADHERENT_ADHERENTS');
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect(
                '_adherent_mandate',
                '_committee_membership',
                '_delegated_access',
                '_zone_based_role',
                '_zone_based_role_zone',
                '_animator_committees',
                '_static_labels',
            )
            ->leftJoin($alias.'.staticLabels', '_static_labels')
            ->leftJoin($alias.'.adherentMandates', '_adherent_mandate')
            ->leftJoin($alias.'.committeeMembership', '_committee_membership')
            ->leftJoin($alias.'.receivedDelegatedAccesses', '_delegated_access')
            ->leftJoin($alias.'.zoneBasedRoles', '_zone_based_role')
            ->leftJoin('_zone_based_role.zones', '_zone_based_role_zone')
            ->leftJoin($alias.'.animatorCommittees', '_animator_committees')
        ;

        return $query;
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
