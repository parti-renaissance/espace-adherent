<?php

namespace App\Form\Admin;

use App\Entity\Jecoute\Question;
use App\Form\Jecoute\ChoiceFormType;
use App\Form\Jecoute\QuestionChoiceType;
use App\Jecoute\SurveyQuestionTypeEnum;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JecouteAdminQuestionType extends AbstractType
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
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (SurveyQuestionTypeEnum::SIMPLE_FIELD === $data['type']) {
                unset($data['choices']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Question::class);
    }
}
