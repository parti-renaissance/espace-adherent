<?php

namespace App\Form\Admin;

use App\Adherent\CertificationRequestBlockCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificationRequestBlockCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'choices' => CertificationRequestBlockCommand::BLOCK_REASONS,
                'choice_label' => function (string $choice) {
                    return "certification_request.block_reason.$choice";
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
        $resolver->setDefault('data_class', CertificationRequestBlockCommand::class);
    }
}
