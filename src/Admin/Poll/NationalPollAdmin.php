<?php

declare(strict_types=1);

namespace App\Admin\Poll;

use App\Entity\Administrator;
use App\Entity\Poll\Poll;
use App\Form\Admin\Poll\PollChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class NationalPollAdmin extends AbstractAdmin
{
    public function __construct(private readonly Security $security)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Poll $poll */
        $poll = $this->getSubject();
        $hasVote = $poll->hasVote();

        $form
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('question', TextType::class, [
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
    protected function prePersist(object $object): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setAdministrator($administrator);
    }
}
