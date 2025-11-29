<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Report\ReportReasonEnum;
use App\Report\ReportCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReportCommand::class,
        ]);
    }
}
