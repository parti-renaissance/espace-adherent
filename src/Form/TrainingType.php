<?php

namespace AppBundle\Form;

use AppBundle\Entity\MemberSummary\Training;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', TextType::class)
            ->add('diploma', TextType::class)
            ->add('study_field', TextType::class)
            ->add('started_at', MonthChoiceType::class, [
                'pre_set_now' => true,
            ])
            ->add('ended_at', MonthChoiceType::class, [
                'required' => false,
            ])
            ->add('on_going', CheckboxType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('extra_curricular', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Training::class,
                'error_mapping' => ['validDuration' => 'on_going'],
            ])
        ;
    }

    public function getParent()
    {
        return SummaryItemType::class;
    }
}
