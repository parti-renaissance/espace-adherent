<?php

namespace App\Form\Jecoute;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataAnswer;
use App\Entity\Jecoute\SurveyQuestion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('textField', TextType::class, [
                'required' => false,
            ])
            ->add('surveyQuestion', EntityType::class, [
                'class' => SurveyQuestion::class,
            ])
            ->add('selectedChoices', EntityType::class, [
                'class' => Choice::class,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', DataAnswer::class);
    }
}
