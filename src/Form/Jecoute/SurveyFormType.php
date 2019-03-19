<?php

namespace AppBundle\Form\Jecoute;

use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\Survey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class SurveyFormType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public const concernedAreaChoices = [
        'Département' => self::DEPARTMENT_CHOICE,
        'Ville' => self::CITY_CHOICE,
    ];

    public const DEPARTMENT_CHOICE = 'department';
    public const CITY_CHOICE = 'city';

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
                ->add('concernedAreaChoice', ChoiceType::class, [
                    'choices' => self::concernedAreaChoices,
                    'expanded' => true,
                    'mapped' => false,
                ])
                ->add('city', TextType::class, [
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData'])
                ->addEventListener(FormEvents::SUBMIT, [$this, 'validateCityByConcernedAreaChoice'])
            ;
        }

        if (!$options['disabled']) {
            $builder
                ->add('published', CheckboxType::class, [
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Survey::class);
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->getData()->getCity()) {
            $form->get('concernedAreaChoice')->setData(self::CITY_CHOICE);
        } else {
            $form->get('concernedAreaChoice')->setData(self::DEPARTMENT_CHOICE);
        }
    }

    public function validateCityByConcernedAreaChoice(FormEvent $event): void
    {
        $form = $event->getForm();

        if (null === $form->get('city')->getData() &&
            self::CITY_CHOICE === $form->get('concernedAreaChoice')->getData()) {
            $form->get('city')->addError(new FormError($this->translator->trans('survey.city.required')));
        }
    }

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
