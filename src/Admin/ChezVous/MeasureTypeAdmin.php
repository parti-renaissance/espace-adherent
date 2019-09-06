<?php

namespace AppBundle\Admin\ChezVous;

use AppBundle\ChezVous\MeasureTypeEvent;
use AppBundle\Events;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
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
            ->add('citizenProjectsLink', UrlType::class, [
                'label' => 'Lien vers les projets citoyens (URL)',
                'required' => false,
            ])
            ->add('ideasWorkshopLink', UrlType::class, [
                'label' => 'Lien vers l\'atelier des idées (URL)',
                'required' => false,
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('label', null, [
                'label' => 'Label',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière modification',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function postUpdate($object)
    {
        $this->dispatcher->dispatch(Events::CHEZVOUS_MEASURE_TYPE_UPDATED, new MeasureTypeEvent($object));
    }

    public function postRemove($object)
    {
        $this->dispatcher->dispatch(Events::CHEZVOUS_MEASURE_TYPE_UPDATED, new MeasureTypeEvent($object));
    }
}
