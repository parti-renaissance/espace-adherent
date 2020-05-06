<?php

namespace App\Form\Jecoute;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\Jecoute\SuggestedQuestionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyQuestionFormType extends AbstractType
{
    private $questionRepository;

    public function __construct(SuggestedQuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (empty($data['fromSuggestedQuestion'])) {
                return;
            }

            if (!$question = $this->questionRepository->findById($data['fromSuggestedQuestion'])) {
                return;
            }

            $data['question'] = [
                'content' => $question->getContent(),
                'type' => $question->getType(),
                'choices' => $question->getChoices()->map(function (Choice $choice) {
                    return [
                        'content' => $choice->getContent(),
                        'position' => $choice->getPosition(),
                    ];
                })
                ->toArray(),
            ];

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', SurveyQuestion::class);
    }
}
