<?php

namespace App\Admin\Extension;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Admin\ReorderableAdminInterface;
use App\Scope\ScopeVisibilityEnum;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EntityScopeVisibilityAdminExtension extends AbstractAdminExtension
{
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $admin = $datagridMapper->getAdmin();

        $datagridMapper
            ->add('visibility', ChoiceFilter::class, [
                'label' => 'Visibilité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ScopeVisibilityEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "scope.visibility.$choice";
                    },
                ],
            ])
            ->add('zone', ZoneAutocompleteFilter::class, [
                'label' => 'Zone',
                'field_options' => [
                    'model_manager' => $admin->getModelManager(),
                    'admin_code' => $admin->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
        ;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('visibility', null, [
                'label' => 'Visibilité',
                'template' => 'admin/scope/list_visibility.html.twig',
            ])
            ->add('zone', null, [
                'label' => 'Zone',
                'template' => 'admin/scope/list_zone.html.twig',
            ])
        ;

        $keys = $listMapper->keys();
        $admin = $listMapper->getAdmin();

        foreach ($admin instanceof ReorderableAdminInterface ? array_merge($admin->getListMapperEndColumns(), ['_action']) : ['_action'] as $column) {
            if (false !== $actionKey = array_search($column, $keys)) {
                unset($keys[$actionKey]);
                $keys[] = $column;
            }
        }

        $listMapper->reorder($keys);
    }
}
