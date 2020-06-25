<?php

namespace App\Admin\ElectedRepresentative;

use App\Address\Address;
use App\ElectedRepresentative\ElectedRepresentativeMandatesOrderer;
use App\ElectedRepresentative\UserListDefinitionHistoryManager;
use App\Election\VoteListNuanceEnum;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\Form\AdherentEmailType;
use App\Form\ElectedRepresentative\SponsorshipType;
use App\Form\GenderType;
use App\Repository\ElectedRepresentative\MandateRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ElectedRepresentativeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    /** @var MandateRepository */
    private $mandateRepository;

    /**
     * @var UserListDefinition[]|array
     */
    private $userListDefinitionsBeforeUpdate;

    private $userListDefinitionHistoryManager;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        MandateRepository $mandateRepository,
        UserListDefinitionHistoryManager $userListDefinitionHistoryManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->mandateRepository = $mandateRepository;
        $this->userListDefinitionHistoryManager = $userListDefinitionHistoryManager;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('show')
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $alias = $query->getRootAlias();

        $query
            ->leftJoin("$alias.mandates", 'mandate')
            ->leftJoin("$alias.politicalFunctions", 'politicalFunction')
            ->leftJoin("$alias.labels", 'label')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('currentMandates', null, [
                'label' => 'Mandats actuels (nuance politique)',
                'template' => 'admin/elected_representative/list_mandates.html.twig',
            ])
            ->add('currentZones', null, [
                'label' => 'Périmètre(s) géographique(s)',
                'template' => 'admin/elected_representative/list_zones.html.twig',
            ])
            ->add('currentPoliticalFunctions', null, [
                'label' => 'Fonctions actuelles',
                'template' => 'admin/elected_representative/list_political_functions.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Qualifications',
                'template' => 'admin/elected_representative/list_type.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('officialId', null, [
                    'label' => 'ID élu officiel',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('emailAddress', EmailType::class, [
                    'mapped' => false,
                    'label' => 'Adresse e-mail de l\'adhérent',
                    'template' => 'admin/elected_representative/show_email.html.twig',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                ])
                ->add('isAdherent', null, [
                    'label' => 'Adhérent',
                    'template' => 'admin/elected_representative/show_is_adherent.html.twig',
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('officialId', null, [
                    'label' => 'ID élu officiel',
                    'disabled' => true,
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('adherent', AdherentEmailType::class, [
                    'required' => false,
                    'label' => 'Adresse e-mail',
                    'help' => 'Attention, changer l\'e-mail ici fera que l\'élu sera associé à un autre compte adhérent.'
                        .' Si vous souhaitez ajouter un autre mail de contact, faites-le ci-dessous.',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                    'required' => false,
                ])
                ->add('isAdherent', ChoiceType::class, [
                    'label' => 'Est adhérent ?',
                    'choices' => [
                        'global.yes' => true,
                        'global.no' => false,
                    ],
                ])
                ->add('adherentPhone', PhoneNumberType::class, [
                    'required' => false,
                    'disabled' => true,
                    'label' => 'Téléphone',
                ])
                ->add('contactPhone', PhoneNumberType::class, [
                    'required' => false,
                    'label' => 'Autre téléphone de contact',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'default_region' => Address::FRANCE,
                    'preferred_country_choices' => [Address::FRANCE],
                    'attr' => ['class' => 'phone'],
                ])
                ->add('birthDate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'required' => false,
                    'label' => 'Lieu de naissance',
                ])
                ->add('userListDefinitions', null, [
                    'label' => 'Labels',
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('uld')
                            ->andWhere('uld.type = :type')
                            ->setParameter('type', UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
                            ->orderBy('uld.label', 'ASC')
                        ;
                    },
                ])
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
                ->add('socialNetworkLinks', 'sonata_type_collection', [
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
                ->add('labels', 'sonata_type_collection', [
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
                ->add('mandates', 'sonata_type_collection', [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.mandate',
                ])
            ->end()
            ->with('Fonctions')
                ->add('politicalFunctions', 'sonata_type_collection', [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.political_function',
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Adherent $adherent */
        $adherent = $form->getData()->getAdherent();
        $adherentEmail = $adherent ? $adherent->getEmailAddress() : null;
        $formAdherentEmail = $data['adherent'] ?: null;

        // for any change of email, 'isAdherent' should be set to true ('oui' value)
        if ($formAdherentEmail && $adherentEmail !== $formAdherentEmail) {
            $data['isAdherent'] = true;
            $event->setData($data);
        }
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => MandateTypeEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $mandate) {
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $politicalFunctions) {
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
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
            ->add('mandates.politicalAffiliation', CallbackFilter::class, [
                'label' => 'Nuance politique',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => VoteListNuanceEnum::toArray(),
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $politicalAffiliation) {
                        $where->add("$alias.politicalAffiliation = :pa_".$politicalAffiliation);
                        $qb->setParameter('pa_'.$politicalAffiliation, $politicalAffiliation);
                    }

                    $qb->andWhere($where);
                    $qb->andWhere("$alias.onGoing = 1");

                    return true;
                },
            ])
            ->add('mandates.zone', CallbackFilter::class, [
                'label' => 'Périmètres géographiques',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => Zone::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->leftJoin($queryBuilder->getRootAlias().'.category', 'category')
                            ->andWhere('category.name != :district')
                            ->setParameter('district', ZoneCategory::DISTRICT)
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();
                    if (!\in_array('zone', $qb->getAllAliases(), true)) {
                        $qb->leftJoin("$alias.$field", 'zone');
                    }

                    /** @var Zone $zone */
                    foreach ($value['value'] as $key => $zone) {
                        switch ($zone->getCategory()->getName()) {
                            case ZoneCategory::REGION:
                                if (!\in_array('referentTag', $qb->getAllAliases())) {
                                    $qb->leftJoin('zone.referentTags', 'referentTag');
                                }

                                $where->add('referentTag IN (:tags)');
                                $qb->setParameter('tags', $zone->getReferentTags());

                                break;
                            case ZoneCategory::DEPARTMENT:
                                if (!\in_array('category', $qb->getAllAliases(), true)) {
                                    $qb->leftJoin('zone.category', 'category');
                                }

                                if (!\in_array('referentTag', $qb->getAllAliases(), true)) {
                                    $qb->leftJoin('zone.referentTags', 'referentTag');
                                }

                                $where->add('(referentTag IN (:tags) AND category.name != :category_name)');
                                $qb
                                    ->setParameter('tags', $zone->getReferentTags())
                                    ->setParameter('category_name', ZoneCategory::REGION)
                                ;

                                break;
                            default:
                                $where->add("$alias.$field = :zone_$key");
                                $qb->setParameter("zone_$key", $zone);
                        }
                    }

                    $qb->andWhere($where);

                    return true;
                },
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere(sprintf('%s.isAdherent = 1', $alias));

                            return true;
                        case 'no':
                            $qb->andWhere(sprintf('%s.isAdherent = 0', $alias));

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('userListDefinitions', ModelAutocompleteFilter::class, [
                'label' => 'Labels',
                'show_filter' => true,
                'field_options' => [
                    'minimum_input_length' => 0,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'label',
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $qb = $datagrid->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb
                            ->andWhere($alias.'.type = :type')
                            ->setParameter('type', UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere('label.name IN (:label_names)');
                    $qb->setParameter('label_names', $value['value']);

                    return true;
                },
            ])
        ;
    }

    /**
     * @param ElectedRepresentative $subject
     */
    public function setSubject($subject)
    {
        if (null === $this->userListDefinitionsBeforeUpdate) {
            $this->userListDefinitionsBeforeUpdate = $subject->getUserListDefinitions()->toArray();
        }

        parent::setSubject($subject);
    }

    /**
     * @param ElectedRepresentative $subject
     */
    public function preUpdate($subject)
    {
        $this->userListDefinitionHistoryManager->handleChanges($subject, $this->userListDefinitionsBeforeUpdate);

        parent::preUpdate($subject);
    }

    public function getExportFields()
    {
        return [
            'Nom' => 'lastName',
            'Prénom' => 'firstName',
            'Mandats actuels (nuance politique)' => 'exportMandates',
            'Fonctions actuelles' => 'exportPoliticalFunctions',
            'Adhérent' => 'exportIsAdherent',
        ];
    }

    public function getExportFormats()
    {
        return ['csv', 'xls'];
    }
}
