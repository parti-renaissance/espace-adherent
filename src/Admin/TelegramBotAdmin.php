<?php

namespace App\Admin;

use App\Entity\TelegramBot;
use App\Form\Admin\StringArrayType;
use Ramsey\Uuid\Uuid;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TelegramBotAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('apiToken', TextType::class, [
                    'label' => 'Clé API',
                ])
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                ])
            ->end()
            ->with('Autorisations', ['class' => 'col-md-6'])
                ->add('blacklistedIds', StringArrayType::class, [
                    'label' => 'Blacklist',
                    'help' => 'Saisir un ID Telegram (user ou group) par ligne.',
                ])
                ->add('whitelistedIds', StringArrayType::class, [
                    'label' => 'Whitelist',
                    'help' => 'Saisir un ID Telegram (user ou group) par ligne.',
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
            ->add('enabled', null, [
                'label' => 'Activé',
                'show_filter' => true,
            ])
            ->add('apiToken', null, [
                'label' => 'Clé API',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Code',
            ])
            ->add('enabled', null, [
                'label' => 'Activé',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    /**
     * @param TelegramBot $object
     */
    protected function postPersist(object $object): void
    {
        $object->secret = Uuid::uuid4();
    }
}
