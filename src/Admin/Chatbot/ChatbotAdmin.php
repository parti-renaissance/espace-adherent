<?php

declare(strict_types=1);

namespace App\Admin\Chatbot;

use App\Chatbot\Telegram\WebhookHandler;
use App\Entity\Chatbot\Chatbot;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChatbotAdmin extends AbstractAdmin
{
    private ?string $telegramBotApiTokenBeforeUpdate = null;

    public function __construct(private readonly WebhookHandler $botWebhookHandler)
    {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'label' => 'Code',
                ])
                ->add('assistantId', TextType::class, [
                    'label' => 'ID Assistant OpenAI',
                ])
                ->add('telegramBotApiToken', TextType::class, [
                    'label' => 'Clé API Bot Telegram',
                    'required' => false,
                    'help' => 'Remplir seulement si ce chatbot est associé à un bot Telegram',
                ])
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, [
                'label' => 'Code',
                'show_filter' => true,
            ])
            ->add('assistantId', null, [
                'label' => 'ID Assistant OpenAI',
                'show_filter' => true,
            ])
            ->add('telegramBotApiToken', null, [
                'label' => 'Clé API Bot Telegram',
                'show_filter' => true,
            ])
            ->add('enabled', null, [
                'label' => 'Activé ?',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('code', null, [
                'label' => 'Code',
            ])
            ->add('assistantId', null, [
                'label' => 'ID Assistant OpenAI',
            ])
            ->add('telegramBotApiToken', null, [
                'label' => 'Clé API Bot Telegram',
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
     * @param Chatbot $object
     */
    protected function alterObject(object $object): void
    {
        $this->telegramBotApiTokenBeforeUpdate = $object->telegramBotApiToken;
    }

    /**
     * @param Chatbot $object
     */
    protected function postPersist(object $object): void
    {
        $this->botWebhookHandler->handleChanges($object);
    }

    /**
     * @param Chatbot $object
     */
    protected function postUpdate(object $object): void
    {
        $this->botWebhookHandler->handleChanges($object, $this->telegramBotApiTokenBeforeUpdate);
    }

    /**
     * @param Chatbot $object
     */
    protected function postRemove(object $object): void
    {
        if ($object->telegramBotApiToken) {
            $this->botWebhookHandler->deleteWebhook($object->telegramBotApiToken);
        }
    }
}
