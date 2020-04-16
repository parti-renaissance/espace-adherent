<?php

namespace App\Form;

use App\AdherentProfile\AdherentProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentFunnelGeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('twitterPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('linkedinPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('telegramPageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('position', ActivityPositionType::class, [
                'required' => false,
                'placeholder' => 'common.i.am',
            ])
            ->add('job', TextType::class, [
                'required' => false,
            ])
            ->add('activityArea', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdherentProfile::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'adherent_profile';
    }
}
