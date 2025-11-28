<?php

declare(strict_types=1);

namespace App\Admin\Ohme;

use App\Entity\Adherent;
use App\Entity\Ohme\Contact;
use App\Ohme\ContactHandler;
use App\Query\Utils\MultiColumnsSearchHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContactAdmin extends AbstractAdmin
{
    private ?Adherent $adherentBeforeUpdate = null;

    public function __construct(private readonly ContactHandler $contactHandler)
    {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('adherent', ModelAutocompleteType::class, [
                    'label' => 'Adhérent',
                    'required' => false,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
                        );
                    },
                    'btn_add' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
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
                            ["$alias.firstname", "$alias.lastname"],
                            ["$alias.lastname", "$alias.firstname"],
                            ["$alias.email", "$alias.email"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                            "$alias.ohmeIdentifier",
                        ]
                    );

                    return true;
                },
            ])
            ->add('has_adherent', CallbackFilter::class, [
                'label' => 'Lié à un adhérent ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.adherent IS NOT NULL");

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere("$alias.adherent IS NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('has_payment', CallbackFilter::class, [
                'label' => 'Paiement(s) lié(s) ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.paymentCount IS NOT NULL");
                            $qb->andWhere("$alias.paymentCount > 0");

                            break;
                        case BooleanType::TYPE_NO:
                            $conditions = $qb->expr()->orX();
                            $conditions->add("$alias.paymentCount IS NULL");
                            $conditions->add("$alias.paymentCount = 0");

                            $qb->andWhere($conditions);
                            break;
                    }

                    return true;
                },
            ])
            ->add('lastPaymentDate', DateRangeFilter::class, [
                'label' => 'Date de dernier paiement',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('fullname', null, [
                'label' => 'Nom',
                'virtual_field' => true,
                'template' => 'admin/ohme/contact/list_fullname.html.twig',
            ])
            ->add('address', null, [
                'label' => 'Adresse',
                'virtual_field' => true,
                'template' => 'admin/ohme/contact/list_address.html.twig',
            ])
            ->add('ohmeCreatedAt', null, [
                'label' => 'Créé le',
            ])
            ->add('ohmeUpdatedAt', null, [
                'label' => 'Modifié le',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
            ])
            ->add('paymentCount', null, [
                'label' => 'Paiement(s)',
            ])
            ->add('lastPaymentDate', null, [
                'label' => 'Dernier paiement',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'ohmeCreatedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param Contact $object
     */
    protected function alterObject(object $object): void
    {
        $this->adherentBeforeUpdate = $object->adherent;
    }

    /**
     * @param Contact $object
     */
    protected function preUpdate(object $object): void
    {
        if ($this->adherentBeforeUpdate !== $object->adherent) {
            $this->contactHandler->updateAdherentLink($object);
        }
    }
}
