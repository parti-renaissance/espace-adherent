<?php

namespace App\Admin;

use App\Form\Jecoute\ChoiceFormType;
use App\Form\Jecoute\QuestionChoiceType;
use App\Jecoute\SurveyQuestionTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class JecouteSuggestedQuestionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Questions du panier', ['class' => 'col-md-6'])
                ->add('content', TextType::class, [
                    'filter_emojis' => true,
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

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('content', null, [
                'label' => 'Question',
            ])
            ->add('type', 'choice', [
                'choices' => array_flip(SurveyQuestionTypeEnum::all()),
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('content', null, [
                'label' => 'Question',
            ])
            ->add('type', 'choice', [
                'choices' => array_flip(SurveyQuestionTypeEnum::all()),
                'label' => 'Type de question',
            ])
            ->add('choices', 'array_list', [
                'label' => 'Réponses',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }
}
