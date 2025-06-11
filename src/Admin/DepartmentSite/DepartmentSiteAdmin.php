<?php

namespace App\Admin\DepartmentSite;

use App\Admin\AbstractAdmin;
use App\Entity\DepartmentSite\DepartmentSite;
use App\Entity\Geo\Zone;
use App\Form\Admin\UnlayerContentType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DepartmentSiteAdmin extends AbstractAdmin
{
    public function __construct(private readonly int $dptSiteUnlayerTemplateId)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'zone.code';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('zone', ModelFilter::class, [
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'multiple' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => ['name', 'code'],
                    'callback' => function (AdminInterface $admin, array $property, $value): void {
                        $datagrid = $admin->getDatagrid();
                        $query = $datagrid->getQuery();
                        $rootAlias = $query->getRootAlias();
                        $query
                            ->andWhere(\sprintf('%1$s.type IN (:types) OR (%1$s.type = :custom_type AND %1$s.code = :zone_fde)', $rootAlias))
                            ->setParameter('types', [Zone::DEPARTMENT])
                            ->setParameter('custom_type', Zone::CUSTOM)
                            ->setParameter('zone_fde', Zone::FDE_CODE)
                        ;

                        $datagrid->setValue($property[0], null, $value);
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('slug', null, [
                'label' => 'Slug',
            ])
            ->add('zone', null, [
                'label' => 'Département',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'preview' => [
                        'template' => 'admin/department_site/list_preview.html.twig',
                    ],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Département', ['class' => 'col-md-6'])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => ['name', 'code'],
                    'label' => 'Département',
                    'btn_add' => false,
                    'callback' => [$this, 'prepareZoneAutocompleteCallback'],
                ])
            ->end()
            ->with('Contenu')
                ->add('jsonContent', HiddenType::class)
                ->add('content', UnlayerContentType::class, [
                    'label' => false,
                    'unlayer_template_id' => $this->dptSiteUnlayerTemplateId,
                ])
            ->end()
        ;
    }

    public static function prepareZoneAutocompleteCallback(
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
            ->andWhere(\sprintf('(%1$s.type = :type OR (%1$s.type = :custom_type AND %1$s.code = :code_fde)) AND %1$s.active = 1', $alias))
            ->setParameter('type', Zone::DEPARTMENT)
            ->setParameter('custom_type', Zone::CUSTOM)
            ->setParameter('code_fde', Zone::FDE_CODE)
        ;
    }

    /**
     * @param DepartmentSite $object
     */
    public function toString(object $object): string
    {
        return \sprintf('Site départemental %s', $object->getSlug());
    }
}
