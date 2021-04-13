<?php

namespace App\Admin\Poll;

use App\Entity\Administrator;
use App\Entity\Poll\Poll;
use App\Form\Admin\Poll\PollChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;

class NationalPollAdmin extends AbstractAdmin
{
    /** @var Security */
    private $security;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('question', null, [
                'label' => 'Question',
                'show_filter' => true,
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('administrator', null, [
                'label' => 'Créé par',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('question', null, [
                'label' => 'Question',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('administrator', null, [
                'label' => 'Créé par',
                'template' => 'admin/poll/list_administrator.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Poll $poll */
        $poll = $this->getSubject();
        $hasVote = $poll->hasVote();

        $formMapper
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('question', TextType::class, [
                    'filter_emojis' => true,
                    'label' => 'Question',
                    'disabled' => $hasVote,
                ])
                ->add('choices', CollectionType::class, [
                    'entry_type' => PollChoiceType::class,
                    'disabled' => $hasVote,
                    'required' => true,
                    'label' => 'Choix',
                    'allow_add' => !$hasVote,
                    'allow_delete' => !$hasVote,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Configuration', ['class' => 'col-md-6'])
                ->add('finishAt', DateTimePickerType::class, [
                    'label' => 'Date de fin',
                ])
            ->end()
        ;
    }

    /**
     * @param Poll $object
     */
    public function prePersist($object)
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setAdministrator($administrator);
    }

    /**
     * @required
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;
    }
}
