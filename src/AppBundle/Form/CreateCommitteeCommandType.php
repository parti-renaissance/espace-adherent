<?php

namespace AppBundle\Form;

use AppBundle\Committee\CommitteeCreationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('address', TextType::class, [
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
            ])
            ->add('country', UnitedNationsCountryType::class)
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => null,
            ])
            ->add('twitterNickname', TextType::class, [
                'required' => false,
            ])
            ->add('googlePlusPageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => null,
            ])
            ->add('acceptConfidentialityTerms', CheckboxType::class, [
                'required' => false,
            ])
            ->add('acceptContactingTerms', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCreationCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'committee';
    }
}
