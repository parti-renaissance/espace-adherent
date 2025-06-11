<?php

namespace App\Admin;

use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsletterSubscriptionAdmin extends AbstractAdmin
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', null, [
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function postRemove(object $object): void
    {
        $this->eventDispatcher->dispatch(new NewsletterEvent($object), Events::UNSUBSCRIBE);
    }

    protected function postUpdate(object $object): void
    {
        $this->eventDispatcher->dispatch(new NewsletterEvent($object), Events::UPDATE);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }
}
