<?php

namespace App\Form\Admin\Extract;

use App\Form\Admin\StringArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractEmailExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emails', StringArrayType::class)
            ->add('fields', ChoiceType::class, [
                'choices' => $this->getFieldChoices(),
                'choice_label' => function (string $choice) {
                    return $this->getTranslationPrefix().$choice;
                },
                'required' => true,
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    abstract protected function getFieldChoices(): array;

    abstract protected function getTranslationPrefix(): string;
}
