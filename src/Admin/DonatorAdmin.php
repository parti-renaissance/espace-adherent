<?php

namespace App\Admin;

use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Donation\DonatorManager;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\DonatorTag;
use App\Entity\Transaction;
use App\Form\Admin\DonatorKinshipType;
use App\Form\GenderType;
use App\Form\ReCountryType;
use App\Repository\DonationRepository;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonatorAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    private $donatorManager;

    public function __construct(string $code, string $class, string $baseControllerName, DonatorManager $donatorManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->donatorManager = $donatorManager;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        return array_merge(parent::configureActionButtons($buttonList, $action, $object), [
            'merge' => [
                'template' => 'admin/donator/merge/merge_button.html.twig',
            ],
            'extract' => [
                'template' => 'admin/donator/extract/extract_button.html.twig',
            ],
            'extract_adherents' => [
                'template' => 'admin/adherent/extract/extract_button.html.twig',
            ],
        ]);
    }

    protected function configureBatchActions(array $actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query->leftJoin('o.donations', 'donations');

        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
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
                    'label' => 'Adresse email',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-6'])
                ->add('city', null, [
                    'label' => 'Ville',
                ])
                ->add('country', ReCountryType::class)
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
                    'entry_options' => [
                        'model_manager' => $this->getModelManager(),
                    ],
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
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
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('donations.nationality', ChoiceFilter::class, [
                'label' => 'Nationalité',
                'show_filter' => true,
                'field_type' => ReCountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('country', ChoiceFilter::class, [
                'label' => 'Pays de résidence',
                'show_filter' => true,
                'field_type' => ReCountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal (préfixe)',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $value = array_map('trim', explode(',', strtolower($value->getValue())));

                    $postalCodeExpression = $qb->expr()->orX();
                    foreach (array_filter($value) as $key => $code) {
                        $postalCodeExpression->add(\sprintf('donations.postAddress.postalCode LIKE :postalCode_%s', $key));
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere(\sprintf('%s.adherent IS NOT NULL', $alias));

                            return true;

                        case 'no':
                            $qb->andWhere(\sprintf('%s.adherent IS NULL', $alias));

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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $dates = $value->getValue();

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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
                'label' => 'Adresse email',
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => static function (array $donator) {
            /** @var Donator $donator */
            $donator = $donator[0];
            $referenceDonation = $donator->getReferenceDonation();
            $adherent = $donator->getAdherent();

            $phone = $adherent instanceof Adherent ? PhoneNumberUtils::format($adherent->getPhone()) : null;

            return [
                'id' => $donator->getId(),
                'Numéro donateur' => $donator->getIdentifier(),
                'Nom' => $donator->getLastName(),
                'Prénom' => $donator->getFirstName(),
                'Civilité' => $donator->getGender(),
                'Adresse email' => $donator->getEmailAddress(),
                'Ville du donateur' => $donator->getCity(),
                'Pays du donateur' => $donator->getCountry(),
                'Adresse de référence' => $referenceDonation?->getAddress(),
                'Code postal de référence' => $referenceDonation?->getPostalCode(),
                'Ville de référence' => $referenceDonation?->getCityName(),
                'Pays de référence' => $referenceDonation?->getCountry(),
                'Nationalité de référence' => $donator->getReferenceNationality(),
                'Tags du donateur' => implode(', ', $donator->getTags()->toArray()),
                'Adhérent' => $adherent instanceof Adherent,
                'Téléphone adhérent' => $phone,
                'Nombre de dons réussis' => $donator->countSuccessfulDonations(),
                'Montant total donné' => $donator->getTotalDonated(),
            ];
        }];
    }

    /**
     * @param Donator $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $object->setIdentifier($this->donatorManager->incrementIdentifier(false));
    }
}
