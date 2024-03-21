<?php

namespace App\Admin\Procuration;

use App\Form\Admin\Procuration\ProxyStatusEnumType;
use App\Procuration\V2\RequestStatusEnum;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProxyAdmin extends AbstractProcurationAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $form
            ->with('Vote')
                ->add('electorNumber', TextType::class, [
                    'label' => 'Numéro d\'électeur',
                ])
                ->add('slots', ChoiceType::class, [
                    'label' => 'Votes disponibles',
                    'expanded' => true,
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                    ],
                ])
            ->end()
            ->with('Traitement', ['class' => 'col-md-6'])
                ->add('status', ProxyStatusEnumType::class, [
                    'label' => 'Statut',
                ])
                ->add('requests', ModelAutocompleteType::class, [
                    'label' => 'Mandants associés',
                    'required' => false,
                    'multiple' => true,
                    'minimum_input_length' => 2,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'btn_add' => false,
                    'by_reference' => false,
                    'callback' => function (AdminInterface $admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $qb = $datagrid->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb
                            ->andWhere($alias.'.status = :status_pending')
                            ->setParameter('status_pending', RequestStatusEnum::PENDING)
                        ;

                        $datagrid->setValue('search', null, $value);
                    },
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('electorNumber', null, [
                'label' => 'Numéro d\'électeur',
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ProxyStatusEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('slots', null, [
                'label' => 'Nombre',
            ])
            ->add('requests', null, [
                'label' => 'Mandants',
                'template' => 'admin/procuration_v2/_list_proxy_requests.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Status',
                'template' => 'admin/procuration_v2/_list_proxy_status.html.twig',
            ])
            ->reorder([
                'id',
                '_fullName',
                'email',
                'phone',
                'voteZone',
                'slots',
                'requests',
                'status',
                'createdAt',
                ListMapper::NAME_ACTIONS,
            ])
        ;
    }
}
