<?php

namespace App\Admin\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Query\Utils\MultiColumnsSearchHelper;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RequestSlotAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Matching')
                ->add('proxySlot', ModelAutocompleteType::class, [
                    'label' => 'Mandataire',
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
                        /** @var QueryBuilder $qb */
                        $qb = $admin->getDatagrid()->getQuery();
                        $alias = $qb->getRootAliases()[0];

                        $qb->join("$alias.proxy", 'proxy');

                        MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                            $qb,
                            $value,
                            [
                                ['proxy.firstNames', 'proxy.lastName'],
                                ['proxy.lastName', 'proxy.firstNames'],
                                ['proxy.email', 'proxy.email'],
                            ],
                            [
                                'proxy.phone',
                            ],
                            [
                                'proxy.id',
                                'proxy.uuid',
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
            ->add('request', null, [
                'label' => 'Mandant',
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

    /**
     * @param Request $object
     */
    protected function alterObject(object $object): void
    {
    }

    /**
     * @param Request $object
     */
    protected function postUpdate(object $object): void
    {
    }

    /** @required */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
