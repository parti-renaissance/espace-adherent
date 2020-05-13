<?php

namespace App\Form;

use App\Newsletter\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'newsletter.invitation.form.first_name',
                    'class' => 'form--half',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'newsletter.invitation.form.last_name',
                    'class' => 'form--half',
                ],
            ])
            ->add('guests', CollectionType::class, [
                'entry_type' => EmailType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'newsletter.invitation.form.guest',
                        'class' => 'form--half',
                    ],
                ],
                'label' => false,
                'allow_add' => false,
                'allow_delete' => false,
                'data' => array_fill(0, 6, ''),
            ])
            ->add('invite', SubmitType::class, [
                'label' => 'newsletter.invitation.form.invite',
                'attr' => ['class' => 'btn btn--blue'],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $invitation = $event->getForm()->getData();
                if (!$invitation instanceof Invitation) {
                    throw new UnexpectedTypeException($invitation, Invitation::class);
                }

                $invitation->filter();
            }, 10)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Invitation::class);
    }
}
