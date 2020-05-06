<?php

namespace App\Form\Jecoute;

use App\Entity\Jecoute\Question;
use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextType::class, [
                'filter_emojis' => true,
                'label' => false,
            ])
            ->add('type', QuestionChoiceType::class)
            ->add('choices', CollectionType::class, [
                'entry_type' => ChoiceFormType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => !$options['disabled'],
                'allow_delete' => !$options['disabled'],
                'by_reference' => false,
                'attr' => [
                    'class' => 'survey-questions-choices-collection',
                ],
                'prototype_name' => '__children_name__',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (SurveyQuestionTypeEnum::SIMPLE_FIELD === $data['type']) {
                $data['choices'] = [];
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Question::class);
    }
}
