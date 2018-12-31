<?php

namespace AppBundle\Form\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Question::class,
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', TextType::class, [
                'label' => 'Catégorie',
                'filter_emojis' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre de question',
                'filter_emojis' => true,
            ])
            ->add('placeholder', TextType::class, [
                'label' => 'Placeholder',
                'filter_emojis' => true,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'scale' => 0,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
                'label' => 'Est obligatoire ?',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Visibilité',
            ])
        ;
    }
}
