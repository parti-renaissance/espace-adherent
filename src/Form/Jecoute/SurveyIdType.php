<?php

declare(strict_types=1);

namespace App\Form\Jecoute;

use App\Form\DataTransformer\SurveyToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyIdType extends AbstractType
{
    private $transformer;

    public function __construct(SurveyToIdTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'survey.unknown_id',
        ]);
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }
}
