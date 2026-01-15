<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\TimelineItemPrivateMessage;
use App\Form\Admin\AdherentAutocompleteType;
use App\JeMengage\Push\Command\PrivateMessageNotificationCommand;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Messenger\MessageBusInterface;

class TimelinePrivateMessageAdmin extends AbstractAdmin
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('title', null, ['label' => 'Titre'])
            ->add('description', null, ['label' => 'Description'])
            ->add('isActive', null, ['label' => 'Affiché'])
            ->add('isNotificationActive', null, ['label' => 'Notification'])
            ->add('countAdherents', null, ['label' => 'Nb. destinataires'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('updatedAt', null, ['label' => 'Date de modification'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isCreation = $this->isCreation();

        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 10]])
                ->add('ctaLabel', null, ['label' => 'Label du CTA', 'required' => false])
                ->add('ctaUrl', null, ['label' => 'URL du CTA', 'required' => false])
                ->add('isActive', null, ['label' => 'Affiché', 'required' => false])
                ->add('source', null, ['label' => 'Source', 'disabled' => true])
            ->end()
            ->with('Notification', ['class' => 'col-md-6'])
                ->add('isNotificationActive', null, ['label' => 'Notification', 'disabled' => !$isCreation])
                ->add('notificationTitle', null, ['label' => 'Titre de la notification', 'required' => false, 'disabled' => !$isCreation])
                ->add('notificationDescription', TextareaType::class, ['label' => 'Contenu de la notification', 'attr' => ['rows' => 10], 'required' => false, 'disabled' => !$isCreation])
                ->add('notificationSentAt', null, ['label' => 'Date d\'envoi de la notification', 'widget' => 'single_text', 'disabled' => true])
            ->end()
            ->with('Militants')
                ->add('adherents', AdherentAutocompleteType::class, [
                    'label' => false,
                    'multiple' => true,
                ])
            ->end()
        ;
    }

    /** @param TimelineItemPrivateMessage|object $object */
    protected function prePersist(object $object): void
    {
        $object->source = 'admin';
    }

    /** @param TimelineItemPrivateMessage|object $object */
    protected function postPersist(object $object): void
    {
        if ($object->isNotificationActive) {
            $this->messageBus->dispatch(new PrivateMessageNotificationCommand($object->getUuid()));
        }
    }
}
