<?php

namespace AppBundle\Admin;

use AppBundle\Donation\DonatorManager;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use AppBundle\Entity\DonatorTag;
use AppBundle\Entity\Transaction;
use AppBundle\Form\Admin\DonatorKinshipType;
use AppBundle\Form\GenderType;
use AppBundle\Form\UnitedNationsCountryType;
use AppBundle\Repository\DonationRepository;
use AppBundle\Utils\PhoneNumberFormatter;
use AppBundle\Utils\PhpConfigurator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonatorAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    private $donatorManager;

    public function __construct(string $code, string $class, string $baseControllerName, DonatorManager $donatorManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->donatorManager = $donatorManager;
    }

    public function configureActionButtons($action, $object = null)
    {
        return array_merge(parent::configureActionButtons($action, $object), [
            'merge' => [
                'template' => 'admin/donator/merge/merge_button.html.twig',
            ],
            'extract' => [
                'template' => 'admin/donator/extract/extract_button.html.twig',
            ],
        ]);
    }

    public function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $query->leftJoin('o.donations', 'donations');

        return $query;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('identifier', null, [
                    'label' => 'Numéro donateur',
                    'disabled' => true,
                    'help' => 'Généré automatiquement à la création',
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-6'])
                ->add('city', null, [
                    'label' => 'Ville',
                ])
                ->add('country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
            ->end()
            ->with('Administration', ['class' => 'col-md-6'])
                ->add('tags', null, [
                    'label' => 'Tags',
                ])
                ->add('comment', null, [
                    'label' => 'Commentaire',
                ])
            ->end()
        ;

        if (!$this->isCurrentRoute('create')) {
            $form
                ->with('Dons', ['class' => 'col-md-12'])
                    ->add('referenceDonation', null, [
                        'label' => false,
                        'expanded' => true,
                        'placeholder' => 'Dernier don en date',
                        'query_builder' => function (DonationRepository $repository) {
                            return $repository->getSubscriptionsForDonatorQueryBuilder($this->getSubject());
                        },
                    ])
                ->end()
            ;
        }

        $form
            ->with('Liens', ['class' => 'col-md-6'])
                ->add('kinships', CollectionType::class, [
                    'entry_type' => DonatorKinshipType::class,
                    'required' => false,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'error_bubbling' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('identifier', null, [
                'label' => 'Numéro donateur',
                'show_filter' => true,
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
            ->add('donations.nationality', ChoiceFilter::class, [
                'label' => 'Nationalité',
                'show_filter' => true,
                'field_type' => CountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('country', ChoiceFilter::class, [
                'label' => 'Pays de résidence',
                'show_filter' => true,
                'field_type' => CountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal (préfixe)',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $value = array_map('trim', explode(',', strtolower($value['value'])));

                    $postalCodeExpression = $qb->expr()->orX();
                    foreach (array_filter($value) as $key => $code) {
                        $postalCodeExpression->add(sprintf('donations.postAddress.postalCode LIKE :postalCode_%s', $key));
                        $qb->setParameter('postalCode_'.$key, $code.'%');
                    }

                    $qb->andWhere($postalCodeExpression);

                    return true;
                },
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
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
                            $qb->andWhere(sprintf('%s.adherent IS NOT NULL', $alias));

                            return true;

                        case 'no':
                            $qb->andWhere(sprintf('%s.adherent IS NULL', $alias));

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('donations.duration', CallbackFilter::class, [
                'label' => 'Type de don',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'ponctual',
                        'recurrent',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'ponctual':
                            $qb
                                ->getQueryBuilder()
                                ->andWhere('donations.duration = :duration')
                                ->setParameter('duration', PayboxPaymentSubscription::NONE)
                            ;

                            return true;

                        case 'recurrent':
                            $qb
                                ->getQueryBuilder()
                                ->andWhere('donations.duration != :duration')
                                ->setParameter('duration', PayboxPaymentSubscription::NONE)
                            ;

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('donations.code', null, [
                'label' => 'Code don',
            ])
            ->add('donations.type', ChoiceFilter::class, [
                'label' => 'Méthode de paiement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => [
                        Donation::TYPE_CB,
                        Donation::TYPE_CHECK,
                        Donation::TYPE_TRANSFER,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
            ])
            ->add('donations.status', ChoiceFilter::class, [
                'label' => 'Statut de don',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => [
                        Donation::STATUS_REFUNDED,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_WAITING_CONFIRMATION,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                ],
            ])
            ->add('lastSuccessfulDonation.lastSuccessDate', DateRangeFilter::class, [
                'label' => 'Date du dernier don',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('donationDate', CallbackFilter::class, [
                'label' => 'Date de don',
                'field_type' => DateRangePickerType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (empty($dates = $value['value'])) {
                        return false;
                    }

                    $start = $dates['start'] ?? null;
                    $end = $dates['end'] ?? null;

                    if (!$start && !$end) {
                        return false;
                    }

                    $qb
                        ->getQueryBuilder()
                        ->leftJoin('donations.transactions', 'transactions')
                    ;

                    if ($start) {
                        $startExpression = $qb
                            ->expr()
                            ->orX()
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add('donations.type = :type_cb')
                                    ->add('transactions.payboxDateTime >= :start_date')
                                    ->add('transactions.payboxResultCode = :success_code')
                            )
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add('donations.type != :type_cb')
                                    ->add('donations.donatedAt >= :start_date')
                            )
                        ;

                        $qb
                            ->andWhere($startExpression)
                            ->setParameter('start_date', $start)
                        ;
                    }

                    if ($end) {
                        $endExpression = $qb
                            ->expr()
                            ->orX()
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add('donations.type = :type_cb')
                                    ->add('transactions.payboxDateTime <= :end_date')
                                    ->add('transactions.payboxResultCode = :success_code')
                            )
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add('donations.type != :type_cb')
                                    ->add('donations.donatedAt <= :end_date')
                            )
                        ;

                        $qb
                            ->andWhere($endExpression)
                            ->setParameter('end_date', $end)
                        ;
                    }

                    $qb->setParameter('type_cb', Donation::TYPE_CB);
                    $qb->setParameter('success_code', Transaction::PAYBOX_SUCCESS);

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'field_options' => [
                    'class' => DonatorTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('identifier', null, [
                'label' => 'Numéro donateur',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
                'template' => 'admin/donator/list_is_adherent.html.twig',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('lastSuccessfulDonation', null, [
                'label' => 'Date du dernier don',
                'template' => 'admin/donator/list_last_donation.html.twig',
                'sortable' => true,
                'sort_field_mapping' => [
                    'fieldName' => 'lastSuccessDate',
                ],
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'lastSuccessfulDonation'],
                ],
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/donator/list_tags.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function getDataSourceIterator()
    {
        PhpConfigurator::disableMemoryLimit();

        return new IteratorCallbackSourceIterator($this->getDonatorIterator(), function (array $donator) {
            /** @var Donator $donator */
            $donator = $donator[0];
            $adherent = $donator->getAdherent();

            $phone = $adherent instanceof Adherent ? PhoneNumberFormatter::format($adherent->getPhone()) : null;

            return [
                'id' => $donator->getId(),
                'Numéro donateur' => $donator->getIdentifier(),
                'Nom' => $donator->getLastName(),
                'Prénom' => $donator->getFirstName(),
                'Civilité' => $donator->getGender(),
                'Adresse e-mail' => $donator->getEmailAddress(),
                'Ville du donateur' => $donator->getCity(),
                'Pays du donateur' => $donator->getCountry(),
                'Adresse de référence' => $donator->getReferenceAddress(),
                'Nationalité de référence' => $donator->getReferenceNationality(),
                'Tags du donateur' => implode(', ', $donator->getTags()->toArray()),
                'Adhérent' => $adherent instanceof Adherent,
                'Téléphone adhérent' => $phone,
            ];
        });
    }

    private function getDonatorIterator(): \Iterator
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $query = $datagrid->getQuery();
        $alias = current($query->getRootAliases());

        $query
            ->select("DISTINCT $alias")
            ->leftJoin("$alias.adherent", 'adherent')
            ->addSelect('adherent')
        ;
        $query->setFirstResult(0);
        $query->setMaxResults(null);

        return $query->getQuery()->iterate();
    }

    /**
     * @param Donator $donator
     */
    public function prePersist($donator)
    {
        parent::prePersist($donator);

        $donator->setIdentifier($this->donatorManager->incrementeIdentifier(false));
    }
}
