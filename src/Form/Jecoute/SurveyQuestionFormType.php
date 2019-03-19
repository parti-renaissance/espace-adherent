<?php

namespace AppBundle\Form\Jecoute;

use AppBundle\Entity\Jecoute\SurveyQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyQuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', QuestionType::class, [
                'disabled' => $options['disabled'],
            ])
            ->add('fromSuggestedQuestion', HiddenType::class)
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'questions-position',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', SurveyQuestion::class);
    }
}
