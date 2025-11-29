<?php

declare(strict_types=1);

namespace App\Form\Jecoute;

use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices' => SurveyQuestionTypeEnum::all(),
                'expanded' => true,
            ])
        ;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
