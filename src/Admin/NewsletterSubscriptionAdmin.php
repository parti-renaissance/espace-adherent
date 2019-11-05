<?php

namespace AppBundle\Admin;

use AppBundle\Newsletter\Events;
use AppBundle\Newsletter\NewsletterEvent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class NewsletterSubscriptionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('email', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'filter_emojis' => true,
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('email', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('email', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function postRemove($object)
    {
        $this->eventDispatcher->dispatch(Events::UNSUBSCRIBE, new NewsletterEvent($object));
    }

    public function postUpdate($object)
    {
        $this->eventDispatcher->dispatch(Events::UPDATE, new NewsletterEvent($object));
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }
}
