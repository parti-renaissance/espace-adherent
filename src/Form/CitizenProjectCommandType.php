<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('address', NullableAddressType::class)
            ->add('assistance_needed', CheckboxType::class, [
                'property_path' => 'assistanceNeeded',
                'required' => false,
            ])
            ->add('assistance_content', TextareaType::class, [
                'required' => false,
                'property_path' => 'assistanceContent',
                'purify_html' => true,
                'filter_emojis' => true,
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA, [$this, 'preSetData']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'citizen_project';
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $citizenProject = $data->getCitizenProject();

        /* @var CitizenProject $citizenProject */
        if (null !== $citizenProject && $citizenProject->isApproved()) {
            $form->add('name', TextType::class, [
                'filter_emojis' => true,
                'disabled' => true,
            ]);
        }
    }
}
