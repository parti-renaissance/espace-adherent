<?php

namespace AppBundle\Form\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\Sponsorship;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sponsorship::class,
        ]);
    }
}
