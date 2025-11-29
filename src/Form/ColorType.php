<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static fn ($value) => $value,
            static fn ($value) => !empty($value) ? '#'.str_replace('#', '', (string) $value) : null
        ));
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
