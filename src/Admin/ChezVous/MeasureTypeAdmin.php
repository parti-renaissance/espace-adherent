<?php

namespace App\Admin\ChezVous;

use App\ChezVous\MeasureTypeEvent;
use App\Events;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MeasureTypeAdmin extends AbstractAdmin
{
    private $dispatcher;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('label', TextType::class, [
                'label' => 'Label',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
            ])
            ->add('updatedAt', DateType::class, [
                'label' => 'Dernière modification',
            ])
            ->add('sourceLink', UrlType::class, [
                'label' => 'Source (URL)',
                'required' => false,
            ])
            ->add('sourceLabel', TextType::class, [
                'label' => 'Source (label)',
                'required' => false,
            ])
            ->add('oldolfLink', UrlType::class, [
                'label' => 'Lien vers transformer.en-marcher.fr (URL)',
                'required' => false,
            ])
            ->add('eligibilityLink', UrlType::class, [
                'label' => 'Lien d\'éligibilité (URL)',
                'required' => false,
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('label', null, [
                'label' => 'Label',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière modification',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function postUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new MeasureTypeEvent($object), Events::CHEZVOUS_MEASURE_TYPE_UPDATED);
    }

    protected function postRemove(object $object): void
    {
        $this->dispatcher->dispatch(new MeasureTypeEvent($object), Events::CHEZVOUS_MEASURE_TYPE_DELETED);
    }
}
