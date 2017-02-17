<?php

namespace AppBundle\Form;

use AppBundle\Entity\JeMarcheReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JeMarcheReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'J\'ai organisé une marche' => JeMarcheReport::TYPE_WALK,
                    'J\'ai effectué du porte a porte' => JeMarcheReport::TYPE_DOOR_TO_DOOR,
                    'J\'ai organisé un diner' => JeMarcheReport::TYPE_DINNER,
                    'J\'ai démarré une correspondance' => JeMarcheReport::TYPE_CORRESPONDENCE,
                    'J\'ai organisé un atelier du projet' => JeMarcheReport::TYPE_WORKSHOP,
                    'J\'ai effectué une action qui me ressemble' => JeMarcheReport::TYPE_ACTION,
                ],
            ])
            ->add('emailAddress', EmailType::class)
            ->add($this->createEmailsListField($builder, 'convinced'))
            ->add($this->createEmailsListField($builder, 'almostConvinced'))
            ->add('notConvinced', IntegerType::class)
            ->add('reaction', TextareaType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JeMarcheReport::class,
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
        $field = $builder->create($fieldName, TextareaType::class);
        $field->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return implode("\n", array_map('trim', $data));
            },
            function ($value) {
                return array_map('trim', explode("\n", $value));
            }
        ));

        return $field;
    }
}
