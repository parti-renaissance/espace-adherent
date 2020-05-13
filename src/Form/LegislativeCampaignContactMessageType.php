<?php

namespace App\Form;

use App\Legislative\LegislativeCampaignContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegislativeCampaignContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emailAddress', EmailType::class, [
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'vous@votre-fai.com',
                ],
            ])
            ->add('firstName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('lastName', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('departmentNumber', TextType::class, [
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => '35',
                ],
            ])
            ->add('electoralDistrictNumber', TextType::class, [
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => '1',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => LegislativeCampaignContactMessage::getRoleChoices(),
            ])
            ->add('recipient', ChoiceType::class, [
                'choices' => LegislativeCampaignContactMessage::getRecipientChoices(),
            ])
            ->add('subject', TextType::class, [
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'Intitulé de votre sujet',
                ],
            ])
            ->add('message', TextareaType::class, [
                'filter_emojis' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LegislativeCampaignContactMessage::class,
        ]);
    }
}
