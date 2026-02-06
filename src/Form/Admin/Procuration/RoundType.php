<?php

declare(strict_types=1);

namespace App\Form\Admin\Procuration;

use App\Entity\Procuration\Round;
use App\Form\Admin\SimpleMDEContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoundType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Round::class,
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', SimpleMDEContent::class, [
                'label' => 'Description',
                'attr' => ['rows' => 20],
                'help' => 'help.markdown',
                'help_html' => true,
            ])
            ->add('date', DateType::class, [
                'years' => range((int) date('Y') - 2, (int) date('Y') + 10),
            ])
        ;
    }
}
