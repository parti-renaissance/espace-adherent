<?php

namespace App\Admin\Procuration;

use App\Query\Utils\MultiColumnsSearchHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProxySlotAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Matching')
                ->add('requestSlot', ModelAutocompleteType::class, [
                    'label' => 'Mandant',
                    'required' => false,
                    'multiple' => false,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [],
                    'callback' => static function (
                        AbstractAdmin $admin,
                        array $properties,
                        string $value
                    ): void {
                        /** @var ProxyQueryInterface $qb */
                        $qb = $admin->getDatagrid()->getQuery();
                        $alias = $qb->getRootAliases()[0];

                        $qb->join("$alias.request", 'request');

                        MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                            $qb->getQueryBuilder(),
                            $value,
                            [
                                ['request.firstNames', 'request.lastName'],
                                ['request.lastName', 'request.firstNames'],
                                ['request.email', 'request.email'],
                            ],
                            [
                                'request.phone',
                            ],
                            [
                                'request.id',
                                'request.uuid',
                            ]
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
                            ["$alias.firstNames", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstNames"],
                            ["$alias.email", "$alias.email"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('round', null, [
                'label' => 'Tour',
                'show_filter' => true,
            ])
            ->add('manual', BooleanFilter::class, [
                'label' => 'Traitement manuel',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('proxy', null, [
                'label' => 'Mandataire',
            ])
            ->add('round', null, [
                'label' => 'Tour',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
