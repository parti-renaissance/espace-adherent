<?php

namespace App\Admin\Team;

use App\Entity\Team\Team;
use App\Form\Admin\Team\MemberAdherentAutocompleteType;
use App\Form\Admin\Team\MemberType;
use App\Team\TeamMemberHistoryManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TeamAdmin extends AbstractAdmin
{
    private ?Team $beforeUpdate = null;

    public function __construct(private readonly TeamMemberHistoryManager $teamMemberHistoryManager)
    {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations ⚙️', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                ])
            ->end()
            ->with('Membres 👥', ['class' => 'col-md-6'])
                ->add('members', CollectionType::class, [
                    'label' => false,
                    'entry_type' => MemberType::class,
                    'entry_options' => [
                        'model_manager' => $this->getModelManager(),
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'error_bubbling' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('members.adherent', ModelFilter::class, [
                'label' => 'Adhérent',
                'show_filter' => true,
                'field_type' => MemberAdherentAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('members', null, [
                'label' => 'Membres',
                'template' => 'admin/team/_list_members.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function alterObject(object $object): void
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $object;
        }
    }

    /**
     * @param Team $object
     */
    protected function postPersist(object $object): void
    {
        $this->teamMemberHistoryManager->handleChanges($object);
    }

    /**
     * @param Team $object
     */
    protected function postUpdate(object $object): void
    {
        $this->teamMemberHistoryManager->handleChanges($object, $this->beforeUpdate);
    }
}
