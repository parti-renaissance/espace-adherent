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
                'attr' => [
                    'placeholder' => 'vous@votre-fai.com',
                ],
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('departmentNumber', TextType::class, [
                'attr' => [
                    'placeholder' => '35',
                ],
            ])
            ->add('electoralDistrictNumber', TextType::class, [
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
                'attr' => [
                    'placeholder' => 'Intitulé de votre sujet',
                ],
            ])
            ->add('message', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LegislativeCampaignContactMessage::class,
        ]);
    }
}
