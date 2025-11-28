<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\FloatToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class AmountType extends AbstractType
{
    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new FloatToStringTransformer());
    }
}
