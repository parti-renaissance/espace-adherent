<?php

declare(strict_types=1);

namespace App\Admin\DepartmentSite;

use App\Admin\AbstractAdmin;
use App\Controller\Admin\ZoneAutocompleteController;
use App\Entity\DepartmentSite\DepartmentSite;
use App\Entity\Geo\Zone;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\UnlayerContentType;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
                'field_type' => AdminZoneAutocompleteType::class,
                'field_options' => [
                    'class' => Zone::class,
                    'multiple' => true,
                    'items_per_page' => 20,
                    'preset' => ZoneAutocompleteController::PRESET_DEPARTMENT_SITE,
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
                ->add('zone', AdminZoneAutocompleteType::class, [
                    'label' => 'Département',
                    'btn_add' => false,
                    'preset' => ZoneAutocompleteController::PRESET_DEPARTMENT_SITE,
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

    /**
     * @param DepartmentSite $object
     */
    public function toString(object $object): string
    {
        return \sprintf('Site départemental %s', $object->getSlug());
    }
}
