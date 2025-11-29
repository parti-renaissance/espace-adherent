<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\UtmFilter;
use App\Donation\DonationEvents;
use App\Donation\Event\DonationWasCreatedEvent;
use App\Donation\Event\DonationWasUpdatedEvent;
use App\Donation\Request\DonationRequestDestinationEnum;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\DonationTag;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Form\ReCountryType;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use League\Flysystem\FilesystemOperator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DonationAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    public function __construct(
        private readonly FilesystemOperator $defaultStorage,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly AdherentRepository $adherentRepository,
    ) {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('edit' === $action) {
            $actions['refund'] = ['template' => 'admin/donation/action_button_refund.html.twig'];
        }

        return $actions;
    }

    protected function preRemove(object $object): void
    {
        if ($object->isCB()) {
            $this->getRequest()->getSession()->getFlashBag()->add('sonata_flash_error', 'Vous ne pouvez pas supprimer un don de type CB');
            throw new ModelManagerException();
        }
    }

    protected function configureBatchActions(array $actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Donation $donation */
        $donation = $this->getSubject();

        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('donator', ModelAutocompleteType::class, [
                    'label' => 'Donateur',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                    'btn_add' => false,
                ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'disabled' => !$this->isCurrentRoute('create'),
                    'choices' => $this->getTypeChoices(),
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ])
                ->add('amountInEuros', FormNumberType::class, [
                    'label' => 'Montant',
                    'disabled' => $donation->getId() && $donation->isCB(),
                ])
                ->add('membership', CheckboxType::class, [
                    'label' => 'Cotisation',
                    'required' => false,
                    'disabled' => $donation->isMembership() || null === $donation->getDonator()?->getAdherent(),
                ])
                ->add('code', null, [
                    'label' => 'Code don',
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => ['name', 'code'],
                    'label' => 'Destination',
                    'help' => 'Sélectionnez un département pour un don local, laissez vide pour un don au siège.',
                    'required' => false,
                    'btn_add' => false,
                    'callback' => [$this, 'prepareDestinationAutocompleteCallback'],
                ])
                ->add('nationality', ReCountryType::class, [
                    'label' => 'Nationalité',
                ])
                ->add('donatedAt', null, [
                    'label' => 'Date du don',
                    'disabled' => $donation->isCB(),
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut du don',
                    'disabled' => $donation->isCB(),
                    'choices' => [
                        Donation::STATUS_WAITING_CONFIRMATION,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_REFUNDED,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                ])
                ->add('checkNumber', null, [
                    'label' => 'Numéro de chèque',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
                ->add('transferNumber', null, [
                    'label' => 'Numéro de virement',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', null, [
                    'label' => 'Rue',
                    'required' => true,
                ])
                ->add('postAddress.additionalAddress', null, ['label' => 'Complément d\'adresse'])
                ->add('postAddress.postalCode', null, [
                    'label' => 'Code postal',
                    'required' => true,
                ])
                ->add('postAddress.cityName', null, [
                    'label' => 'Ville',
                    'required' => true,
                ])
                ->add('postAddress.country', ReCountryType::class, [
                    'label' => 'Pays',
                    'required' => true,
                ])
            ->end()
            ->with('Fichier', ['class' => 'col-md-6'])
                ->add('file', FileType::class, [
                    'required' => false,
                    'label' => 'Ajoutez un fichier',
                    'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                ])
                ->add('removeFile', CheckboxType::class, [
                    'label' => 'Supprimer le fichier ?',
                    'required' => false,
                ])
            ->end()
            ->with('Administration', ['class' => 'col-md-6'])
                ->add('tags', null, ['label' => 'Tags'])
                ->add('comment', null, ['label' => 'Commentaire'])
                ->add('utmSource', null, ['label' => 'UTM Source'])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne'])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('donator', ModelFilter::class, [
                'label' => 'Donateur',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ],
            ])
            ->add('code', null, ['label' => 'Code don'])
            ->add('utmSource', null, ['label' => 'UTM Source'])
            ->add('utmCampaign', null, ['label' => 'UTM Campagne'])
            ->add('zone', CallbackFilter::class, [
                'label' => 'Destination',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => DonationRequestDestinationEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "donation.destination.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
                        case DonationRequestDestinationEnum::LOCAL:
                            $qb->andWhere("$alias.zone IS NOT NULL");

                            return true;

                        case DonationRequestDestinationEnum::NATIONAL:
                            $qb->andWhere("$alias.zone IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('membership', CallbackFilter::class, [
                'label' => 'Cotisation ?',
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere("$alias.membership = TRUE");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.membership = FALSE");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getTypeChoices(),
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                    'multiple' => true,
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        Donation::STATUS_WAITING_CONFIRMATION,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_REFUNDED,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                    'multiple' => true,
                ],
            ])
            ->add('date', CallbackFilter::class, [
                'label' => 'Date de don',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (empty($dates = $value->getValue())) {
                        return false;
                    }

                    $start = $dates['start'] ?? null;
                    $end = $dates['end'] ?? null;

                    if (!$start && !$end) {
                        return false;
                    }

                    $qb
                        ->getQueryBuilder()
                        ->leftJoin("$alias.transactions", 'transactions')
                    ;

                    if ($start) {
                        $startExpression = $qb
                            ->expr()
                            ->orX()
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add("$alias.type = :type_cb")
                                    ->add('transactions.payboxDateTime >= :start_date')
                            )
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add("$alias.type != :type_cb")
                                    ->add("$alias.donatedAt >= :start_date")
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
                                    ->add("$alias.type = :type_cb")
                                    ->add('transactions.payboxDateTime <= :end_date')
                            )
                            ->add(
                                $qb
                                    ->expr()
                                    ->andX()
                                    ->add("$alias.type != :type_cb")
                                    ->add("$alias.donatedAt <= :end_date")
                            )
                        ;

                        $qb
                            ->andWhere($endExpression)
                            ->setParameter('end_date', $end)
                        ;
                    }

                    $qb->setParameter('type_cb', Donation::TYPE_CB);

                    return true;
                },
            ])
            ->add('minAmount', CallbackFilter::class, [
                'label' => 'Montant minimum',
                'show_filter' => true,
                'field_type' => FormNumberType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->getValue() || !is_numeric($value->getValue())) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.amount >= :min_amount")
                        ->setParameter('min_amount', $value->getValue() * 100)
                    ;

                    return true;
                },
            ])
            ->add('maxAmount', CallbackFilter::class, [
                'label' => 'Montant maximum',
                'show_filter' => true,
                'field_type' => FormNumberType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->getValue() || !is_numeric($value->getValue())) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.amount <= :max_amount")
                        ->setParameter('max_amount', $value->getValue() * 100)
                    ;

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'show_filter' => true,
                'field_options' => [
                    'class' => DonationTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('nationality', ChoiceFilter::class, [
                'label' => 'Nationalité',
                'show_filter' => true,
                'field_type' => ReCountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('postAddress.country', ChoiceFilter::class, [
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
                        return;
                    }

                    $value = array_map('trim', explode(',', strtolower($value->getValue())));

                    $postalCodeExpression = $qb->expr()->orX();
                    foreach (array_filter($value) as $key => $code) {
                        $postalCodeExpression->add(\sprintf('%s.postAddress.postalCode LIKE :postalCode_%s', $alias, $key));
                        $qb->setParameter('postalCode_'.$key, $code.'%');
                    }

                    $qb->andWhere($postalCodeExpression);

                    return true;
                },
            ])
            ->add('utm', UtmFilter::class, ['label' => 'UTM Source / Campagne', 'show_filter' => true])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('donator', null, [
                'label' => 'Donateur',
            ])
            ->add('amountInEuros', null, [
                'label' => 'Montant',
                'template' => 'admin/donation/list_amount.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/donation/list_type.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut du don',
                'template' => 'admin/donation/list_status.html.twig',
            ])
            ->add('code', null, [
                'label' => 'Code don',
            ])
            ->add('zone', null, [
                'label' => 'Destination',
                'template' => 'admin/donation/list_destination.html.twig',
            ])
            ->add('donatedAt', null, [
                'label' => 'Date',
            ])
            ->add('utm', null, [
                'label' => 'UTM',
                'virtual_field' => true,
                'template' => 'admin/CRUD/list/utm_list.html.twig',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/donation/list_tags.html.twig',
            ])
            ->add('membership', null, [
                'label' => 'Cotisation',
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

        return [IteratorCallbackDataSource::CALLBACK => static function (array $donation) {
            /** @var Donation $donation */
            $donation = $donation[0];
            $donator = $donation->getDonator();
            $referenceDonation = $donator->getReferenceDonation();
            $adherent = $donator->getAdherent();

            $phone = $adherent instanceof Adherent ? PhoneNumberUtils::format($adherent->getPhone()) : null;

            $destination = $donation->isNationalVisibility()
                ? 'Siège'
                : \sprintf('Mixte (%s)', $donation->getZone()->getCode());

            return [
                'id' => $donation->getId(),
                'Montant' => $donation->getAmountInEuros(),
                'Code don' => $donation->getCode(),
                'Destination' => $destination,
                'Date' => $donation->getCreatedAt()->format('Y/m/d H:i:s'),
                'Type' => $donation->getType(),
                'Don récurrent' => $donation->hasSubscription(),
                'Status' => $donation->getStatus(),
                'Nationalité' => $donation->getNationality(),
                'Adresse' => $donation->getAddress(),
                'Code postal' => $donation->getPostalCode(),
                'Ville' => $donation->getCityName(),
                'Pays' => $donation->getCountry(),
                'Numéro donateur' => $donator->getIdentifier(),
                'Nom' => $donator->getLastName(),
                'Prénom' => $donator->getFirstName(),
                'Civilité' => $donator->getGender(),
                'Adresse email' => $donator->getEmailAddress(),
                'Ville du donateur' => $donator->getCity(),
                'Pays du donateur' => $donator->getCountry(),
                'Adresse de référence' => $referenceDonation?->getAddress(),
                'Complément d\'adresse de référence' => $referenceDonation?->getAdditionalAddress(),
                'Code postal de référence' => $referenceDonation?->getPostalCode(),
                'Ville de référence' => $referenceDonation?->getCityName(),
                'Pays de référence' => $referenceDonation?->getCountry(),
                'Tags du donateur' => implode(', ', $donator->getTags()->toArray()),
                'Transactions' => $donation->hasSubscription() ? implode(', ', $donation->getTransactions()->toArray()) : null,
                'Adhérent' => $adherent instanceof Adherent,
                'Téléphone adhérent' => $phone,
                'Cotisation' => $donation->isMembership(),
                'UTM Source' => $donation->utmSource,
                'UTM Campagne' => $donation->utmCampaign,
            ];
        }];
    }

    /**
     * @param Donation $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $this->handleFile($object);

        $this->dispatcher->dispatch(new DonationWasCreatedEvent($object), DonationEvents::CREATED);

        $this->handleAdherentMembership($object);
    }

    /**
     * @param Donation $object
     */
    protected function preUpdate(object $object): void
    {
        parent::preUpdate($object);

        $this->handleFile($object);

        $this->dispatcher->dispatch(new DonationWasUpdatedEvent($object), DonationEvents::UPDATED);

        $this->handleAdherentMembership($object);
    }

    /**
     * @param Donation $object
     */
    protected function postUpdate(object $object): void
    {
        parent::postUpdate($object);

        if ($adherent = $object->getDonator()?->getAdherent()) {
            $this->refreshAdherent($adherent);
        }
    }

    /**
     * @param Donation $object
     */
    protected function postRemove(object $object): void
    {
        parent::postRemove($object);

        if ($object->isMembership() && $adherent = $object->getDonator()?->getAdherent()) {
            $this->refreshAdherent($adherent);
        }

        if ($object->hasFileUploaded()) {
            $filePath = $object->getFilePathWithDirectory();

            if ($this->defaultStorage->has($filePath)) {
                $this->defaultStorage->delete($filePath);
            }
        }
    }

    protected function createNewInstance(): object
    {
        /** @var Donation $donation */
        $donation = parent::createNewInstance();
        $donation->setPostAddress(PostAddress::createEmptyAddress());

        return $donation;
    }

    private function refreshAdherent(Adherent $adherent): void
    {
        $this->adherentRepository->refreshDonationDates($adherent);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED_IN_ADMIN);
    }

    private function handleAdherentMembership(Donation $donation): void
    {
        if (
            $donation->isMembership()
            && $donation->isFinished()
            && ($adherent = $donation->getDonator()->getAdherent())
        ) {
            $adherent->donatedForMembership($donation->getDonatedAt());
            $adherent->setSource(MembershipSourceEnum::RENAISSANCE);
            $adherent->setPapUserRole(true);
        }
    }

    public function handleFile(Donation $donation): void
    {
        $filepath = $donation->getFilePathWithDirectory();

        if ($donation->getRemoveFile() && $this->defaultStorage->has($filepath)) {
            $this->defaultStorage->delete($filepath);
            $donation->removeFilename();

            return;
        }

        $this->uploadFile($donation);
    }

    public function uploadFile(Donation $donation): void
    {
        $uploadedFile = $donation->getFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(\sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $donation->setFilenameFromUploadedFile();

        $this->defaultStorage->write($donation->getFilePathWithDirectory(), file_get_contents($donation->getFile()->getPathname()));
    }

    private function getTypeChoices(): array
    {
        $choices = [
            Donation::TYPE_CHECK,
            Donation::TYPE_TRANSFER,
            Donation::TYPE_TPE,
        ];

        if (!$this->isCurrentRoute('create')) {
            $choices[] = Donation::TYPE_CB;
        }

        return $choices;
    }

    public static function prepareDestinationAutocompleteCallback(
        AdminInterface $admin,
        array $properties,
        string $value,
    ): void {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }
        $qb
            ->orWhere($orx)
            ->andWhere(\sprintf('%1$s.type = :type AND %1$s.active = 1', $alias))
            ->setParameter('type', Zone::DEPARTMENT)
        ;
    }
}
