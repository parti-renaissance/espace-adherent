<?php

namespace App\Form;

use App\Entity\CitizenProjectCategorySkill;
use App\Entity\CitizenProjectSkill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectCategorySkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('skill', EntityType::class, [
                'label' => false,
                'class' => CitizenProjectSkill::class,
            ])
            ->add('promotion', CheckboxType::class, [
                'required' => false,
                'label' => 'Promotion',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CitizenProjectCategorySkill::class);
    }
}
