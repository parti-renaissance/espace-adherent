<?php

namespace App\Form\Jecoute;

use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => SurveyQuestionTypeEnum::all(),
                'expanded' => true,
            ])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
