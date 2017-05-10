<?php

namespace AppBundle\Form;

use AppBundle\Donation\PayboxPaymentFrequency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationFrequencyRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frequency', ChoiceType::class, [
                'choices' => $options['donation_frequencies'],
                'choice_label' => function ($frequency) {
                    return PayboxPaymentFrequency::fromInteger($frequency)->getLabelFrequency();
                },
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Continuer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('donation_frequencies');
        $resolver->setAllowedTypes('donation_frequencies', 'array');
    }
}
