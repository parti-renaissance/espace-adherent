<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Jecoute\ChoiceFormType;
use App\Form\Jecoute\QuestionChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class JecouteSuggestedQuestionAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Questions du panier', ['class' => 'col-md-6'])
                ->add('content', TextType::class, [
                    'label' => 'Question',
                ])
                ->add('type', QuestionChoiceType::class, [
                    'label' => 'Type de question',
                ])
                ->add('choices', CollectionType::class, [
                    'entry_type' => ChoiceFormType::class,
                    'required' => false,
                    'label' => 'Réponses',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publier la question panier',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('content', null, [
                'label' => 'Question',
            ])
            ->add('type', null, [
                'label' => 'Type de question',
            ])
            ->add('choices', 'array_list', [
                'label' => 'Réponses',
            ])
            ->add('published', null, [
                'label' => 'Publié',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('content', null, [
                'label' => 'Question',
            ])
            ->add('type', null, [
                'label' => 'Type de question',
            ])
            ->add('choices', 'array_list', [
                'label' => 'Réponses',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('edit');
    }
}
