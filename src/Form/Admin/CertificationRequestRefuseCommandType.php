<?php

namespace App\Form\Admin;

use App\Adherent\CertificationRequestRefuseCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificationRequestRefuseCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'choices' => CertificationRequestRefuseCommand::REFUSAL_REASONS,
                'choice_label' => function (string $choice) {
                    return "certification_request.refusal_reason.$choice";
                },
            ])
            ->add('customReason', TextareaType::class, [
                'required' => false,
            ])
            ->add('comment', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', CertificationRequestRefuseCommand::class);
    }
}
