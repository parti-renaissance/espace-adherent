<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProjectCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('category', EventCategoryType::class, [
                'class' => CitizenProjectCategory::class,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
            ])
            ->add('proposed_solution', TextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('address', NullableAddressType::class, [
                'required' => false,
            ])
            ->add('assistance_needed', CheckboxType::class, [
                'property_path' => 'assistanceNeeded',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'citizen_project';
    }
}
