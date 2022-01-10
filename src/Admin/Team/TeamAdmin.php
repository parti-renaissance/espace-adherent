<?php

namespace App\Admin\Team;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Team\Team;
use App\Form\Admin\Team\MemberAdherentAutocompleteType;
use App\Form\Admin\Team\MemberType;
use App\Scope\ScopeVisibilityEnum;
use App\Team\TeamMemberHistoryManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                    'help' => 'Laissez vide pour créer une équipe nationale.',
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
            ->add('visibility', ChoiceFilter::class, [
                'label' => 'Visibilité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ScopeVisibilityEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "team.visibility.$choice";
                    },
                ],
            ])
            ->add('zone', ZoneAutocompleteFilter::class, [
                'label' => 'Zone',
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
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
            ->add('visibility', null, [
                'label' => 'Visibilité',
                'template' => 'admin/team/_list_visibility.html.twig',
            ])
            ->add('zone', null, [
                'label' => 'Zone',
                'template' => 'admin/team/_list_zone.html.twig',
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
