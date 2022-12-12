<?php

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Admin\VotingPlatform\DesignationAdmin;
use App\Entity\Geo\Zone;
use App\Entity\LocalElection\LocalElection;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\AdminType;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;

class LocalElectionAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('designation.label')
            ->add('designation', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Désignation',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => 'label',
                    'to_string_callback' => function (Designation $designation) {
                        return $designation->getLabel();
                    },
                ],
            ])
            ->add('designation.zones', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Zones',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'code',
                    ],
                    'callback' => function (AdminInterface $admin, array $property, $value): void {
                        $datagrid = $admin->getDatagrid();
                        $query = $datagrid->getQuery();
                        $rootAlias = $query->getRootAlias();
                        $query
                            ->andWhere($rootAlias.'.type IN (:types)')
                            ->setParameter('types', [Zone::DEPARTMENT, Zone::REGION])
                        ;

                        $datagrid->setValue($property[0], null, $value);
                    },
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('designation', AdminType::class, [
                    'label' => false,
                    'form_type' => DesignationAdmin::FORM_TYPE_LOCAL_ELECTION,
                ])
            ->end()
        ;
    }

    /** @param LocalElection $object */
    protected function alterNewInstance(object $object): void
    {
        $object->setDesignation($designation = new Designation());
        $designation->setType(DesignationTypeEnum::LOCAL_ELECTION);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('designation.id', null, ['label' => 'Désignation id'])
            ->addIdentifier('designation.label', null, ['label' => 'Libellé'])
            ->add('designation.zones', null, ['label' => 'Zones'])
            ->add('status', 'trans', [
                'label' => 'Statut',
                'format' => 'designation.status.%s',
            ])
            ->add('designation.voteStartDate', null, ['label' => 'Vote le'])
            ->add('designation.updatedAt', null, ['label' => 'Date de modification'])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    /** @param LocalElection $object */
    public function toString($object): string
    {
        return $object->getLabel();
    }
}
