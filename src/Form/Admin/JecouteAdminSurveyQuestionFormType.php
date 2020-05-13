<?php

namespace App\Form\Admin;

use App\Entity\Jecoute\SurveyQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JecouteAdminSurveyQuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', JecouteAdminQuestionType::class)
            ->add('fromSuggestedQuestion', HiddenType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var SurveyQuestion $data */
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof SurveyQuestion && $data->getFromSuggestedQuestion()) {
                $form->add('question', JecouteAdminQuestionType::class, [
                    'attr' => ['class' => 'question-disabled'],
                    'disabled' => true,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', SurveyQuestion::class);
    }
}
