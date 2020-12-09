<?php

namespace App\Form\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Survey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => SurveyQuestionFormType::class,
                'entry_options' => [
                    'label' => false,
                    'disabled' => $options['disabled'],
                ],
                'allow_add' => !$options['disabled'],
                'allow_delete' => !$options['disabled'],
                'by_reference' => false,
                'attr' => [
                    'class' => 'survey-questions-collection',
                ],
                'prototype_name' => '__parent_name__',
            ])
        ;

        if ($builder->getData() instanceof LocalSurvey) {
            $builder
                ->add('zone', EntityType::class, [
                    'class' => Zone::class,
                    'choices' => $options['zones'],
                ])
            ;

            if (!$options['disabled'] && $options['edit_by_author']) {
                $builder
                    ->add('blockedChanges', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }
        }

        if (!$options['disabled']) {
            $builder->add('published', CheckboxType::class, [
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['zones', 'edit_by_author'])
            ->setAllowedTypes('zones', [Zone::class.'[]'])
            ->setAllowedTypes('edit_by_author', 'bool')
            ->setDefaults([
                'data_class' => Survey::class,
                'zones' => [],
                'edit_by_author' => false,
            ])
        ;
    }
}
