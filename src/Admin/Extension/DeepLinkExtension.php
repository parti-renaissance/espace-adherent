<?php

namespace App\Admin\Extension;

use App\Admin\ReorderableAdminInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\ListMapper;

class DeepLinkExtension extends AbstractAdminExtension
{
    public function configureListFields(ListMapper $list): void
    {
        $list->add('dynamicLink', null, [
            'label' => 'Lien de partage',
            'template' => 'admin/CRUD/list_dynamic_link.html.twig',
        ]);

        $keys = $list->keys();
        $admin = $list->getAdmin();

        foreach ($admin instanceof ReorderableAdminInterface ? array_merge($admin->getListMapperEndColumns(), ['_actions']) : ['_actions'] as $column) {
            if (false !== $actionKey = array_search($column, $keys)) {
                unset($keys[$actionKey]);
                $keys[] = $column;
            }
        }

        $list->reorder($keys);
    }
}
