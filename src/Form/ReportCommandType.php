<?php

namespace AppBundle\Form;

use AppBundle\Entity\Report\ReportReasonEnum;
use AppBundle\Report\ReportCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasons', ChoiceType::class, [
                'choices' => array_combine(ReportReasonEnum::REASONS_LIST, ReportReasonEnum::REASONS_LIST),
                'choice_translation_domain' => 'reports',
                'choice_name' => function ($choice) {
                    return $choice;
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('comment', TextareaType::class, [
                'filter_emojis' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportCommand::class,
        ]);
    }
}
