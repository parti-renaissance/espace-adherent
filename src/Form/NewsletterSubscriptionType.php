<?php

namespace AppBundle\Form;

use AppBundle\Entity\NewsletterSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterSubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('country', UnitedNationsCountryType::class, [
                'required' => false,
            ])
            ->add('personalDataCollection', AcceptPersonalDataCollectType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsletterSubscription::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_newsletter_subscription';
    }
}
