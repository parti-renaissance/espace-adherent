<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use Doctrine\ORM\QueryBuilder;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ResourceLinkAdmin extends AbstractAdmin
{
    use SortableAdminTrait;

    protected $baseRoutePattern = 'app/jecoute-resource-link';
    protected $baseRouteName = 'admin_app_jecoute_resource_link';

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var QueryBuilder $query */
        $query->addOrderBy('o.position', 'ASC');

        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
             ->add('label', null, [
                 'label' => 'Label',
                 'show_filter' => true,
             ])
             ->add('url', null, [
                 'label' => 'Url',
                 'show_filter' => true,
             ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('label', null, [
                'label' => 'Label',
            ])
            ->add('url', null, [
                'label' => 'Url',
            ])
            ->add('_image', 'thumbnail', [
                'label' => 'Photo',
                'virtual_field' => true,
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                ])
                ->add('url', UrlType::class, [
                    'label' => 'Url',
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                ])
            ->end()
        ;
    }
}
