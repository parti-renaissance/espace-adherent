<?php

namespace App\Form\Coalition;

use App\Entity\Coalition\Cause;
use App\Form\AdherentUuidType;
use App\Form\PurifiedTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CauseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('description', PurifiedTextareaType::class, [
                'required' => false,
                'filter_emojis' => true,
                'with_character_count' => true,
            ])
            ->add('coalition', EnabledCoalitionEntityType::class)
            ->add('secondCoalition', EnabledCoalitionEntityType::class, [
                'required' => false,
            ])
            ->add('author', AdherentUuidType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Cause::class,
            ])
        ;
    }
}
