<?php

namespace App\Form;

use App\Entity\Invite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('firstName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('email', EmailType::class)
            ->add('message', TextareaType::class, [
                'filter_emojis' => true,
            ])
            ->add('personalDataCollection', AcceptPersonalDataCollectType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invite::class,
            'translation_domain' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_invitation';
    }
}
