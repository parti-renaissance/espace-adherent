<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\Team\Team;
use App\Form\Admin\MemberAdherentAutocompleteType;
use App\Form\Admin\MemberType;
use App\Team\TeamMemberHistoryManager;
use App\Team\TypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;

class TeamAdmin extends AbstractAdmin
{
    private $security;
    private $teamMemberHistoryManager;

    /** @var Team|null */
    private $beforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName = null,
        Security $security,
        TeamMemberHistoryManager $teamMemberHistoryManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->security = $security;
        $this->teamMemberHistoryManager = $teamMemberHistoryManager;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations ⚙️', ['class' => 'col-md-6'])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => TypeEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "team.type.$choice";
                    },
                ])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                    'filter_emojis' => true,
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
            ->add('type', ChoiceFilter::class, [
                'show_filter' => true,
                'label' => 'Type',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => TypeEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "team.type.$choice";
                    },
                    'multiple' => true,
                ],
            ])
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
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/team/_list_type.html.twig',
            ])
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
    public function prePersist($object)
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setAdministrator($administrator);
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
