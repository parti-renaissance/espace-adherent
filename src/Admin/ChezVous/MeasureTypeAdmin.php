<?php

namespace AppBundle\Admin\ChezVous;

use AppBundle\Producer\ChezVous\AlgoliaProducer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class MeasureTypeAdmin extends AbstractAdmin
{
    /**
     * @var AlgoliaProducer
     */
    private $algoliaProducer;

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
        $this->algoliaProducer->dispatchMeasureTypeUpdate($object);
    }

    public function setAlgoliaProducer(AlgoliaProducer $algoliaProducer): void
    {
        $this->algoliaProducer = $algoliaProducer;
    }
}
