<?php

namespace App\Form;

use App\Event\EventInvitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'event.invitation.form.email',
                    'class' => 'form--full',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'event.invitation.form.first_name',
                    'class' => 'form--full',
                ],
                'filter_emojis' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'event.invitation.form.last_name',
                    'class' => 'form--full',
                ],
                'filter_emojis' => true,
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'event.invitation.form.message',
                    'class' => 'form--full',
                ],
                'label' => false,
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('guests', CollectionType::class, [
                'entry_type' => EmailType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'event.invitation.form.guest',
                        'class' => 'form--full',
                    ],
                ],
                'label' => false,
                'allow_delete' => false,
                'required' => false,
                'data' => array_fill(0, 3, ''),
            ])
            ->add('invite', SubmitType::class, [
                'label' => 'event.invitation.form.invite',
                'attr' => ['class' => 'btn btn--blue'],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $invitation = $event->getForm()->getData();
                if (!$invitation instanceof EventInvitation) {
                    throw new UnexpectedTypeException($invitation, EventInvitation::class);
                }

                $invitation->filter();
            }, 10)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', EventInvitation::class);
    }
}
