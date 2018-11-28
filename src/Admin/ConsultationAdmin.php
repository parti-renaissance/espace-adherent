<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ConsultationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'startedAt',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
            ])
            ->add('startedAt', null, [
                'label' => 'Du',
            ])
            ->add('endedAt', null, [
                'label' => 'Au',
            ])
            ->add('responseTime', null, [
                'label' => 'Temps de réponse',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Consultation')
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('url', null, [
                    'label' => 'URL',
                ])
                ->add('responseTime', null, [
                    'label' => 'Temps de réponse',
                ])
            ->end()
            ->with('Période')
                ->add('startedAt', DateTimePickerType::class, [
                    'label' => 'Du',
                ])
                ->add('endedAt', DateTimePickerType::class, [
                    'label' => 'Au',
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('url', null, [
                'label' => 'URL',
                'show_filter' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('url', null, [
                'label' => 'URL',
            ])
            ->add('startedAt', null, [
                'label' => 'Période',
                'template' => 'admin/consultation/list_period.html.twig',
            ])
            ->add('responseTime', null, [
                'label' => 'Temps de réponse',
                'template' => 'admin/consultation/list_response_time.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
