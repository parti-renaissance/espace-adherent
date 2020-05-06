<?php

namespace App\Form;

use App\Entity\JeMarcheReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JeMarcheReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Un kiosque' => JeMarcheReport::TYPE_KIOSQUE,
                    'Une marche' => JeMarcheReport::TYPE_WALK,
                    'un porte-à-porte' => JeMarcheReport::TYPE_DOOR_TO_DOOR,
                    'Un dîner' => JeMarcheReport::TYPE_DINNER,
                    'Une conversation' => JeMarcheReport::TYPE_CONVERSATION,
                    'Un atelier du programme' => JeMarcheReport::TYPE_WORKSHOP,
                    'Une action qui me ressemble' => JeMarcheReport::TYPE_ACTION,
                ],
            ])
            ->add('emailAddress', EmailType::class)
            ->add('postalCode', TextType::class)
            ->add($this->createEmailsListField($builder, 'convinced'))
            ->add($this->createEmailsListField($builder, 'almostConvinced'))
            ->add('notConvinced', IntegerType::class, ['required' => false])
            ->add('reaction', TextareaType::class, [
                'required' => false,
                'filter_emojis' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JeMarcheReport::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_je_marche';
    }

    private function createEmailsListField(FormBuilderInterface $builder, $fieldName): FormBuilderInterface
    {
        $field = $builder->create($fieldName, TextareaType::class, [
            'required' => false,
        ]);

        $field->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return implode("\n", array_map('trim', $data));
            },
            function ($value) {
                return array_filter(array_map('trim', explode("\n", $value)));
            }
        ));

        return $field;
    }
}
