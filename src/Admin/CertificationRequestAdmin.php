<?php

namespace AppBundle\Admin;

use AppBundle\Entity\CertificationRequest;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CertificationRequestAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('adherent.firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('adherent.lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('adherent.emailAddress', null, [
                'label' => 'Email',
                'show_filter' => true,
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        CertificationRequest::STATUS_PENDING,
                        CertificationRequest::STATUS_APPROVED,
                        CertificationRequest::STATUS_REFUSED,
                    ],
                    'choice_label' => function (string $choice) {
                        return "certification_request.status.$choice";
                    },
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/certification_request/list_adherent.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/certification_request/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }
}
