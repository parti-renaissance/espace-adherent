<?php

namespace App\Admin\Chatbot;

use App\Form\Admin\Chatbot\AssistantTypeEnumType;
use App\Form\Admin\Chatbot\ChatbotTypeEnumType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChatbotAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-12'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
            ->end()
            ->with('Integration', ['class' => 'col-md-6'])
                ->add('type', ChatbotTypeEnumType::class, [
                    'label' => 'Type',
                ])
                ->add('telegramBot', ModelType::class, [
                    'label' => 'Bot Telegram',
                    'required' => false,
                ])
            ->end()
            ->with('Assistant', ['class' => 'col-md-6'])
                ->add('assistantType', AssistantTypeEnumType::class, [
                    'label' => 'Type d\'assistant',
                ])
                ->add('openAiAssistant', ModelType::class, [
                    'label' => 'Assistant OpenAI',
                    'required' => false,
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
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChatbotTypeEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('assistantType', ChoiceFilter::class, [
                'label' => 'Type d\'assistant',
                'show_filter' => true,
                'field_type' => AssistantTypeEnumType::class,
                'field_options' => [
                    'multiple' => true,
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
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/chatbot/list_type.html.twig',
            ])
            ->add('assistantType', null, [
                'label' => 'Type d\'assistant',
                'template' => 'admin/chatbot/list_assistantType.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
