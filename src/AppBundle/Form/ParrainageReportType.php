<?php

namespace AppBundle\Form;

use AppBundle\Entity\ParrainageReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParrainageReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Un kiosque' => ParrainageReport::TYPE_KIOSQUE,
                    'Une marche' => ParrainageReport::TYPE_WALK,
                    'un porte-à-porte' => ParrainageReport::TYPE_DOOR_TO_DOOR,
                    'Un dîner' => ParrainageReport::TYPE_DINNER,
                    'Une conversation' => ParrainageReport::TYPE_CONVERSATION,
                    'Un atelier du programme' => ParrainageReport::TYPE_WORKSHOP,
                    'Une action qui me ressemble' => ParrainageReport::TYPE_ACTION,
                ],
            ])
            ->add('emailAddress', EmailType::class)
            ->add('postalCode', TextType::class)
            ->add($this->createEmailsListField($builder, 'convinced'))
            ->add($this->createEmailsListField($builder, 'almostConvinced'))
            ->add('notConvinced', IntegerType::class, ['required' => false])
            ->add('reaction', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParrainageReport::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
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
