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
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TeamAdmin extends AbstractAdmin
{
    private $teamMemberHistoryManager;

    /** @var Team|null */
    private $beforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName = null,
        TeamMemberHistoryManager $teamMemberHistoryManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->teamMemberHistoryManager = $teamMemberHistoryManager;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations ⚙️', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
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

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('members.adherent', ModelAutocompleteFilter::class, [
                'label' => 'Adhérent',
                'show_filter' => true,
                'field_type' => MemberAdherentAutocompleteType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('members', null, [
                'label' => 'Membres',
                'template' => 'admin/team/_list_members.html.twig',
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

    /**
     * @param Team $subject
     */
    public function setSubject($subject)
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $subject;
        }

        parent::setSubject($subject);
    }

    /**
     * @param Team $object
     */
    public function postPersist($object)
    {
        $this->teamMemberHistoryManager->handleChanges($object);
    }

    /**
     * @param Team $object
     */
    public function postUpdate($object)
    {
        $this->teamMemberHistoryManager->handleChanges($object, $this->beforeUpdate);
    }
}
