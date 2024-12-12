<?php

namespace App\Form\ElectedRepresentative;

use App\Entity\ElectedRepresentative\Sponsorship;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('presidentialElectionYear', TextType::class, [
                'disabled' => true,
                'label' => false,
            ])
            ->add('candidate', TextType::class, [
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sponsorship::class,
        ]);
    }
}
