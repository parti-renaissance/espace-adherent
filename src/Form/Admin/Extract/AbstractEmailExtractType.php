<?php

namespace App\Form\Admin\Extract;

use App\Form\DataTransformer\StringToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractEmailExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emails', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 10,
                ],
            ])
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

        $builder
            ->get('emails')
            ->addModelTransformer(new StringToArrayTransformer(\PHP_EOL))
        ;
    }

    abstract protected function getFieldChoices(): array;

    abstract protected function getTranslationPrefix(): string;
}
