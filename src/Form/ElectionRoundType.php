<?php

namespace AppBundle\Form;

use AppBundle\Entity\ElectionRound;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionRoundType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ElectionRound::class,
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class)
            ->add('description', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('date', DateType::class, [
                'years' => range((int) date('Y') - 10, (int) date('Y') + 10),
            ])
        ;
    }
}
