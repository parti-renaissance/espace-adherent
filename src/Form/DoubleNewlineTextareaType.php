<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\DoubleNewlineTextTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class DoubleNewlineTextareaType extends AbstractType
{
    private $doubleNewlineTextTransformer;

    public function __construct(DoubleNewlineTextTransformer $doubleNewlineTextTransformer)
    {
        $this->doubleNewlineTextTransformer = $doubleNewlineTextTransformer;
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->doubleNewlineTextTransformer);
    }
}
