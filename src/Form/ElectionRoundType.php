<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ElectionRound;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionRoundType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ElectionRound::class,
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class)
            ->add('description', TextType::class)
            ->add('date', DateType::class, [
                'years' => range((int) date('Y') - 10, (int) date('Y') + 10),
            ])
        ;
    }
}
