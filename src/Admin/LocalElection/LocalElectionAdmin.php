<?php

declare(strict_types=1);

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Admin\VotingPlatform\Designation\DesignationAdmin;
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
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;

class LocalElectionAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('designation.label')
            ->add('designation', ModelFilter::class, [
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
            ->add('designation.voteStartDate', DateRangeFilter::class, [
                'label' => 'Date de début de vote',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('designation.voteEndDate', DateRangeFilter::class, [
                'label' => 'Date de fin de vote',
                'field_type' => DateRangePickerType::class,
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

        if (!$this->isCurrentRoute('create')) {
            $form
                ->with('Listes', ['class' => 'col-md-6'])
                    ->add('candidaciesGroups', null, [
                        'label' => false,
                        'disabled' => true,
                    ])
                ->end()
            ;
        }
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
            ->add('designation.zones', 'array', [
                'label' => 'Zones',
                'virtual_field' => true,
                'template' => 'admin/local_election/list_zone.html.twig',
            ])
            ->add('candidaciesGroups', null, [
                'label' => 'Nombre de listes',
                'virtual_field' => true,
                'template' => 'admin/local_election/list_candidacies_groups_count.html.twig',
            ])
            ->add('status', 'trans', [
                'label' => 'Statut',
                'format' => 'designation.status.%s',
            ])
            ->add('designation.voteStartDate', null, ['label' => 'Vote le'])
            ->add('designation.voteEndDate', null, ['label' => 'Clôture du vote'])
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
