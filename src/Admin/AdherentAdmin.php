<?php

namespace App\Admin;

use App\AdherentProfile\AdherentProfileHandler;
use App\Admin\Filter\AdherentRoleFilter;
use App\Admin\Filter\ReferentTagAutocompleteFilter;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Adherent;
use App\Entity\AdherentTag;
use App\Entity\BaseGroup;
use App\Entity\BoardMember\BoardMember;
use App\Entity\BoardMember\Role;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\Instance\InstanceQuality;
use App\Entity\SubscriptionType;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Form\ActivityPositionType;
use App\Form\Admin\AdherentInstanceQualityType;
use App\Form\Admin\AdherentTerritorialCouncilMembershipType;
use App\Form\Admin\AdherentZoneBasedRoleType;
use App\Form\Admin\CandidateManagedAreaType;
use App\Form\Admin\JecouteManagedAreaType;
use App\Form\Admin\LreAreaType;
use App\Form\Admin\MunicipalChiefManagedAreaType;
use App\Form\Admin\ReferentManagedAreaType;
use App\Form\Admin\SenatorAreaType;
use App\Form\Admin\SenatorialCandidateManagedAreaType;
use App\Form\EventListener\BoardMemberListener;
use App\Form\EventListener\CoalitionModeratorRoleListener;
use App\Form\EventListener\RevokeManagedAreaSubscriber;
use App\Form\GenderType;
use App\History\EmailSubscriptionHistoryHandler;
use App\Instance\InstanceQualityScopeEnum;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentProfileWasUpdatedEvent;
use App\Membership\Event\UserEvent;
use App\Membership\MandatesEnum;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Repository\Instance\InstanceQualityRepository;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentAdmin extends AbstractAdmin
{
    protected $accessMapping = [
        'ban' => 'BAN',
        'certify' => 'CERTIFY',
        'uncertify' => 'UNCERTIFY',
        'extract' => 'EXTRACT',
        'create_renaissance' => 'CREATE_RENAISSANCE',
    ];

    private $dispatcher;
    private $emailSubscriptionHistoryManager;
    /** @var PoliticalCommitteeManager */
    private $politicalCommitteeManager;
    /** @var InstanceQualityRepository */
    private $instanceQualityRepository;
    private AdherentProfileHandler $adherentProfileHandler;
    private LoggerInterface $logger;

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
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryManager,
        PoliticalCommitteeManager $politicalCommitteeManager,
        AdherentProfileHandler $adherentProfileHandler,
        LoggerInterface $logger
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->emailSubscriptionHistoryManager = $emailSubscriptionHistoryManager;
        $this->politicalCommitteeManager = $politicalCommitteeManager;
        $this->adherentProfileHandler = $adherentProfileHandler;
        $this->logger = $logger;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('ban', $this->getRouterIdParameter().'/ban')
            ->add('certify', $this->getRouterIdParameter().'/certify')
            ->add('uncertify', $this->getRouterIdParameter().'/uncertify')
            ->add('extract', 'extract')
            ->add('send_resubscribe_email', $this->getRouterIdParameter().'/send-resubscribe-email')
            ->add('create_renaissance', 'create-renaissance')
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        if (\in_array($action, ['ban', 'certify', 'uncertify'], true)) {
            $actions = parent::configureActionButtons('show', $object);
        } else {
            $actions = parent::configureActionButtons($action, $object);
        }

        if (\in_array($action, ['edit', 'show', 'ban', 'certify', 'uncertify'], true)) {
            $actions['switch_user'] = ['template' => 'admin/adherent/action_button_switch_user.html.twig'];
        }

        if (\in_array($action, ['edit', 'show'], true)) {
            if ($this->canAccessObject('ban', $object) && $this->hasRoute('ban')) {
                $actions['ban'] = ['template' => 'admin/adherent/action_button_ban.html.twig'];
            }

            if ($this->canAccessObject('certify', $object) && $this->hasRoute('certify')) {
                $actions['certify'] = ['template' => 'admin/adherent/action_button_certify.html.twig'];
            }

            if ($this->canAccessObject('uncertify', $object) && $this->hasRoute('uncertify')) {
                $actions['uncertify'] = ['template' => 'admin/adherent/action_button_uncertify.html.twig'];
            }
        }

        $actions['extract'] = ['template' => 'admin/adherent/extract/extract_button.html.twig'];
        $actions['create_renaissance'] = ['template' => 'admin/adherent/renaissance/create_button.html.twig'];

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
                ->add('certifiedAt', null, [
                    'label' => 'Certifié le',
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
                ->add('municipalChiefManagedArea', null, [
                    'label' => 'Candidat Municipales 2020 🇫🇷',
                    'required' => false,
                ])
                ->add('isJecouteManager', 'boolean', [
                    'label' => 'Est responsable des questionnaires ?',
                ])
                ->add('jecouteManagedArea.zone', null, [
                    'label' => 'Responsable des questionnaires',
                ])
            ->end()
            ->with('Mandat électif', ['class' => 'col-md-3'])
                ->add('isDeputy', 'boolean', [
                    'label' => 'Est un(e) député(e) ?',
                ])
                ->add('deputyZone', null, [
                    'label' => 'Circonscription député',
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
            ->with('Adresse', ['class' => 'col-md-6'])
                ->add('postAddress.address', null, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', null, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', null, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', null, [
                    'label' => 'Longitude',
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-3'])
                ->add('isRegionalCoordinator', 'boolean', [
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
            ->tab('Général')
                ->with('Informations personnelles', ['class' => 'col-md-6'])
                    ->add('status', ChoiceType::class, [
                        'label' => 'Etat du compte',
                        'choices' => [
                            'Activé' => Adherent::ENABLED,
                            'Désactivé' => Adherent::DISABLED,
                        ],
                    ])
                    ->add('tags', ModelType::class, [
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
                        'format_identity_case' => true,
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => 'Prénom',
                        'format_identity_case' => true,
                    ])
                    ->add('nickname', TextType::class, [
                        'label' => 'Pseudo',
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
                    ->add('birthdate', DatePickerType::class, [
                        'label' => 'Date de naissance',
                        'required' => false,
                    ])
                    ->add('position', ActivityPositionType::class, [
                        'label' => 'Statut',
                    ])
                    ->add('mandates', ChoiceType::class, [
                        'label' => 'adherent.mandate.admin.label',
                        'choices' => MandatesEnum::CHOICES,
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
                ->with('Abonnement', ['class' => 'col-md-6'])
                    ->add('subscriptionTypes', null, [
                        'label' => 'Notifications via e-mail et mobile :',
                        'choice_label' => 'label',
                        'required' => false,
                        'multiple' => true,
                    ])
                ->end()
                ->with('Identité de l\'élu', [
                    'class' => 'col-md-6',
                    'description' => 'adherent.admin.elected_representative.description',
                    'box_class' => 'box box-success',
                ])
                    ->add('electedRepresentative', TextType::class, [
                        'label' => false,
                        'required' => false,
                        'mapped' => false,
                    ])
                ->end()
                ->with('Coalitions', ['class' => 'col-md-6'])
                    ->add('isCoalitionModeratorRole', CheckboxType::class, [
                        'label' => 'Responsable Coalition',
                        'required' => false,
                        'mapped' => false,
                    ])
                ->end()
                ->with('Responsabilités locales', ['class' => 'col-md-6'])
                    ->add('managedArea', ReferentManagedAreaType::class, [
                        'label' => false,
                        'required' => false,
                    ])
                    ->add('lreArea', LreAreaType::class, [
                        'label' => 'La république ensemble',
                        'required' => false,
                    ])
                    ->add('jecouteManagedArea', JecouteManagedAreaType::class, [
                        'label' => 'jecoute_manager',
                        'required' => false,
                        'help' => "Laisser vide si l'adhérent n'est pas responsable des questionnaires. Choisissez un département, un arrondissement de Paris ou une circonscription des Français établis hors de France",
                        'model_manager' => $this->getModelManager(),
                    ])
                    ->add('printPrivilege', null, [
                        'label' => 'Accès à "La maison des impressions"',
                        'required' => false,
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
                ->with('Élections 🇫🇷', ['class' => 'col-md-6'])
                    ->add('municipalChiefManagedArea', MunicipalChiefManagedAreaType::class, [
                        'label' => 'Candidat Municipales 2020 🇫🇷',
                        'help' => <<<HELP
                            Laisser vide si l'adhérent n'est pas chef municipal.
                            Utiliser les codes INSEE des villes (54402 pour NORROY-LE-SEC). <br/>
                            Utiliser <strong>75100</strong> pour la ville de Paris,
                            <strong>13200</strong> - Marseille, <strong>69380</strong> - Lyon
                            HELP,
                    ])
                    ->add('senatorialCandidateManagedArea', SenatorialCandidateManagedAreaType::class, [
                        'label' => 'Candidat Sénatoriales 2020',
                    ])
                    ->add('candidateManagedArea', CandidateManagedAreaType::class, [
                        'label' => 'Candidat',
                    ])
                    ->add('procurationManagedAreaCodesAsString', TextType::class, [
                        'label' => 'coordinator.label.codes',
                        'required' => false,
                        'help' => <<<HELP
                            Laisser vide si l'adhérent n'est pas responsable procuration. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.<br/>
                            Utiliser le tag <strong>ALL</strong> pour cibler toutes les zones géographiques.
                            HELP
                        ,
                    ])
                    ->add('assessorManagedAreaCodesAsString', TextType::class, [
                        'label' => 'assessors_manager',
                        'required' => false,
                        'help' => <<<HELP
                            Laisser vide si l'adhérent n'est pas responsable assesseur. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.<br/>
                            Utiliser le tag <strong>ALL</strong> pour cibler toutes les zones géographiques.
                            HELP
,
                    ])
                    ->add('electionResultsReporter', null, [
                        'label' => 'Accès au formulaire de remontée des résultats du ministère de l\'Intérieur',
                        'required' => false,
                    ])
                ->end()
                ->with('Mandat électif', ['class' => 'col-md-6'])
                    ->add('senatorArea', SenatorAreaType::class, [
                        'required' => false,
                        'label' => 'Circonscription sénateur',
                        'help' => 'Laisser vide si l\'adhérent n\'est pas parlementaire.',
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
                    ->add('boardMemberRoles', ModelType::class, [
                        'expanded' => true,
                        'multiple' => true,
                        'btn_add' => false,
                        'class' => Role::class,
                        'mapped' => false,
                        'help' => 'Laisser vide si l\'adhérent n\'est pas membre du Conseil.',
                    ])
                ->end()
                ->with('Responsable communauté thématique', ['class' => 'col-md-6'])
                    ->add('handledThematicCommunities', EntityType::class, [
                        'label' => 'Communautés thématiques',
                        'class' => ThematicCommunity::class,
                        'required' => false,
                        'multiple' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er
                                ->createQueryBuilder('tc')
                                ->andWhere('tc.enabled = 1')
                            ;
                        },
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
                ->with('Adresse', ['class' => 'col-md-6'])
                    ->add('postAddress.address', TextType::class, [
                        'label' => 'Rue',
                    ])
                    ->add('postAddress.postalCode', TextType::class, [
                        'label' => 'Code postal',
                    ])
                    ->add('postAddress.cityName', TextType::class, [
                        'label' => 'Ville',
                    ])
                    ->add('postAddress.country', CountryType::class, [
                        'label' => 'Pays',
                    ])
                    ->add('postAddress.latitude', TextType::class, [
                        'label' => 'Latitude',
                    ])
                    ->add('postAddress.longitude', TextType::class, [
                        'label' => 'Longitude',
                    ])
                ->end()
                ->with('Zone expérimentale 🚧', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-warning',
                ])
                    ->add('canaryTester')
                ->end()
            ->end()
            ->tab('Instances')
                ->with('Membre du Conseil territorial et CoPol', [
                    'class' => 'col-md-6 territorial-council-member-info',
                    'description' => 'territorial_council.admin.description',
                ])
                    ->add('territorialCouncilMembership', AdherentTerritorialCouncilMembershipType::class, [
                        'label' => false,
                        'invalid_message' => 'Un adhérent ne peut être membre que d\'un seul Conseil territorial.',
                    ])
                ->end()
        ;

        if ($this->isGranted('CONSEIL')) {
            $formMapper
                ->with('Conseil national', ['class' => 'col-md-6'])
                    ->add('instanceQualities', AdherentInstanceQualityType::class, [
                        'by_reference' => false,
                        'label' => false,
                    ])
                    ->add('voteInspector', null, ['label' => 'Inspecteur de vote', 'required' => false])
                ->end()
            ;
        }

        $formMapper
            ->end()
            ->tab('Rôles locaux')
                ->with(false)
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
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventSubscriber(new BoardMemberListener())
            ->addEventSubscriber(new CoalitionModeratorRoleListener())
            ->addEventSubscriber(new RevokeManagedAreaSubscriber())
        ;

        $formMapper
            ->get('procurationManagedAreaCodesAsString')
            ->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return $data;
            },
            function ($value) {
                return strtoupper($value);
            }
        ))
        ;

        $formMapper
            ->get('assessorManagedAreaCodesAsString')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return $data;
                },
                function ($value) {
                    return strtoupper($value);
                }
            ))
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
            ->add('mailchimpStatus', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ContactStatusEnum::values(),
                    'choice_label' => function (string $label) {
                        return 'mailchimp_contact.status.'.$label;
                    },
                ],
                'label' => 'Abonnement e-mail',
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
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
            ->add('nickname', null, [
                'label' => 'Pseudo',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('lastLoggedAt', DateRangeFilter::class, [
                'label' => 'Dernière connexion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
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
                'field_type' => CountryType::class,
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
            ->add('status', null, ['label' => 'Etat du compte'], ChoiceType::class, [
                'choices' => [
                    'Activé' => Adherent::ENABLED,
                    'Désactivé' => Adherent::DISABLED,
                ],
            ])
            ->add('adherent', null, [
                'label' => 'Est adhérent ?',
            ])
            ->add('referentTags', ModelAutocompleteFilter::class, [
                'label' => 'Tags souscrits',
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
            ->add('managedArea', ReferentTagAutocompleteFilter::class, [
                'label' => 'Tags gérés',
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
            ->add('role', AdherentRoleFilter::class)
            ->add('mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) (legacy)',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => MandatesEnum::CHOICES,
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
            ->add('elected_representative_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) RNE',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => MandateTypeEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->leftJoin(ElectedRepresentative::class, 'er', Expr\Join::WITH, sprintf('%s.id = er.adherent', $alias))
                        ->leftJoin('er.mandates', 'mandate')
                        ->andWhere('mandate.finishAt IS NULL')
                        ->andWhere('mandate.onGoing = 1')
                        ->andWhere('mandate.isElected = 1')
                        ->andWhere('mandate.type IN (:types)')
                        ->setParameter('types', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('adherent_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) internes',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge(
                        TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS,
                        ['TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT]
                    ),
                    'choice_label' => function (string $choice) {
                        if ('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $choice) {
                            return 'territorial_council.membership.quality.elected_candidate_adherent';
                        } else {
                            return 'political_committee.membership.quality.'.$choice;
                        }
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $mandatesCondition = 'adherentMandate.quality IN (:qualities)';
                    if (\in_array('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, $value['value'])) {
                        $mandatesCondition = '(adherentMandate.quality IN (:qualities) OR adherentMandate.committee IS NOT NULL)';
                    }

                    $qb
                        ->leftJoin("$alias.adherentMandates", 'adherentMandate')
                        ->andWhere('adherentMandate.finishAt IS NULL')
                        ->andWhere($mandatesCondition)
                        ->setParameter('qualities', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('instanceQualities', CallbackFilter::class, [
                'label' => 'Membre du Conseil national',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge([
                        'Oui' => true,
                        'Non' => false,
                    ], array_combine($qualities = $this->instanceQualityRepository->getAllCustomQualities(), $qualities)),
                    'group_by' => function ($choice) {
                        if (\is_bool($choice)) {
                            return 'Général';
                        }

                        return 'Qualités personnalisées';
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (null === $value['value']) {
                        return false;
                    }

                    $qb
                        ->leftJoin("$alias.instanceQualities", 'adherent_instance_quality')
                        ->leftJoin('adherent_instance_quality.instanceQuality', 'instance_quality', Expr\Join::WITH, 'FIND_IN_SET(:national_council_scope, instance_quality.scopes) > 0')
                        ->andWhere('instance_quality.id '.(0 === $value['value'] ? 'IS NULL' : 'IS NOT NULL'))
                        ->setParameter('national_council_scope', InstanceQualityScopeEnum::NATIONAL_COUNCIL)
                    ;

                    if ($value['value'] instanceof InstanceQuality) {
                        $qb
                            ->andWhere('instance_quality = :instance_quality')
                            ->setParameter('instance_quality', $value['value'])
                        ;
                    }

                    return true;
                },
            ])
            ->add('memberships.committee', CallbackFilter::class, [
                'label' => 'Comité de vote',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => Committee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.status = :approved')
                            ->setParameter('approved', BaseGroup::APPROVED)
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.committee IN (:committees)")
                        ->andWhere("$alias.enableVote = 1")
                        ->setParameter('committees', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('territorialCouncilMembership.territorialCouncil', CallbackFilter::class, [
                'label' => 'Conseil territorial',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => TerritorialCouncil::class,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'codes',
                    ],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.territorialCouncil IN (:tc)")
                        ->setParameter('tc', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('politicalCommitteeMembership.politicalCommittee', CallbackFilter::class, [
                'label' => 'Comité politique',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => PoliticalCommittee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.isActive = 1')
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.politicalCommittee IN (:pc)")
                        ->setParameter('pc', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('municipalChiefManagedArea.jecouteAccess', null, ['label' => 'Candidat municipal: Accès J\'écoute'])
            ->add('municipalChiefManagedArea.inseeCode', null, ['label' => 'Candidat municipal: Insee code'])
            ->add('activeMembership', CallbackFilter::class, [
                'label' => 'Cotisation à jour',
                'show_filter' => true,
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere("$alias.lastMembershipDonation IS NOT NULL");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.lastMembershipDonation IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('lastMembershipDonation', DateRangeFilter::class, [
                'label' => 'Date de dernière cotisation',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('renaissanceMembership', CallbackFilter::class, [
                'label' => 'Renaissance',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => RenaissanceMembershipFilterEnum::CHOICES,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case RenaissanceMembershipFilterEnum::ADHERENT_OR_SYMPATHIZER_RE:
                            $qb
                                ->andWhere("$alias.source = :source_renaissance")
                                ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                            ;

                            return true;
                        case RenaissanceMembershipFilterEnum::ADHERENT_RE:
                            $qb
                                ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NOT NULL")
                                ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                            ;

                            return true;
                        case RenaissanceMembershipFilterEnum::SYMPATHIZER_RE:
                            $qb
                                ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NULL")
                                ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                            ;

                            return true;
                        case RenaissanceMembershipFilterEnum::OTHERS_ADHERENT:
                            $qb
                                ->andWhere("$alias.source != :source_renaissance OR $alias.source IS NULL")
                                ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                            ;

                            return true;
                        default:
                            return false;
                    }
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
        $this->dispatcher->dispatch(new UserEvent($this->beforeUpdate), UserEvents::USER_BEFORE_UPDATE);
    }

    /**
     * @param Adherent $object
     */
    public function postUpdate($object)
    {
        $this->adherentProfileHandler->updateReferentTagsAndSubscriptionHistoryIfNeeded($object);

        $this->dispatcher->dispatch(new AdherentProfileWasUpdatedEvent($object), AdherentEvents::PROFILE_UPDATED);
        $this->emailSubscriptionHistoryManager->handleSubscriptionsUpdate($object, $this->beforeUpdate->getSubscriptionTypes());
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($object, $this->beforeUpdate->getTerritorialCouncilMembership());

        $this->dispatcher->dispatch(new UserEvent($object), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
        $this->dispatcher->dispatch(new UserEvent($object), UserEvents::USER_UPDATED);
        $this->dispatcher->dispatch(new UserEvent($object), UserEvents::USER_UPDATED_IN_ADMIN);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('lastName', null, [
                'label' => 'Nom Prénom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('postAddress', null, [
                'label' => 'Ville (CP) Pays',
                'template' => 'admin/adherent/list_postaddress.html.twig',
                'header_style' => 'min-width: 75px',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('lastLoggedAt', null, [
                'label' => 'Dernière connexion',
            ])
            ->add('instances', null, [
                'label' => 'Instances de vote',
                'virtual_field' => true,
                'header_style' => 'min-width: 150px',
                'template' => 'admin/adherent/list_vote_instances.html.twig',
            ])
            ->add('referentTags', null, [
                'label' => 'Tags souscrits',
                'associated_property' => 'code',
            ])
            ->add('managedAreaTags', null, [
                'label' => 'Tags gérés',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_managed_area_tags.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Rôles',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
            ->add('lastMembershipDonation', null, [
                'label' => 'Dernière cotisation',
            ])
            ->add('mailchimpStatus', null, [
                'label' => 'Abonnement',
                'template' => 'admin/adherent/list_email_subscription_status.html.twig',
            ])
            ->add('allMandates', null, [
                'label' => 'Mandats',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_mandates.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }

    public function getDataSourceIterator()
    {
        PhpConfigurator::disableMemoryLimit();

        return new IteratorCallbackSourceIterator(
            $this->getAdherentIterator(),
            function (array $adherent) {
                /** @var Adherent $adherent */
                $adherent = $adherent[0];

                try {
                    $phone = PhoneNumberUtils::format($adherent->getPhone());
                    $birthDate = $adherent->getBirthdate();
                    $registeredAt = $adherent->getRegisteredAt();

                    return [
                        'UUID' => $adherent->getUuid(),
                        'Email' => $adherent->getEmailAddress(),
                        'Prénom' => $adherent->getFirstName(),
                        'Nom' => $adherent->getLastName(),
                        'Date de naissance' => $birthDate ? $birthDate->format('Y/m/d H:i:s') : null,
                        'Téléphone' => $phone,
                        'Inscrit(e) le' => $registeredAt ? $registeredAt->format('Y/m/d H:i:s') : null,
                        'Sexe' => $adherent->getGender(),
                        'Adresse' => $adherent->getAddress(),
                        'Code postal' => $adherent->getPostalCode(),
                        'Ville' => $adherent->getCityName(),
                        'Pays' => $adherent->getCountry(),
                    ];
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf('Error exporting Adherent with UUID: %s. (%s)', $adherent->getUuid(), $e->getMessage()),
                        ['exception' => $e]
                    );

                    return [
                        'UUID' => $adherent->getUuid(),
                        'Email' => $adherent->getEmailAddress(),
                    ];
                }
            }
        );
    }

    private function getAdherentIterator(): \Iterator
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $query = $datagrid->getQuery();
        $alias = current($query->getRootAliases());

        $query
            ->select("DISTINCT $alias")
        ;
        $query->setFirstResult(0);
        $query->setMaxResults(null);

        return $query->getQuery()->iterate();
    }

    /** @required */
    public function setInstanceQualityRepository(InstanceQualityRepository $instanceQualityRepository): void
    {
        $this->instanceQualityRepository = $instanceQualityRepository;
    }
}
