<?php

namespace App\Form\ApplicationRequest;

use App\ApplicationRequest\Filter\ListFilter;
use App\Entity\ApplicationRequest\Theme;
use App\Form\GenderType;
use App\Repository\ApplicationRequest\ThemeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationRequestListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('gender', GenderType::class, [
                'placeholder' => 'common.all',
                'expanded' => true,
                'required' => false,
            ])
            ->add('isAdherent', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'common.all',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'required' => false,
                'placeholder' => 'common.all',
                'query_builder' => static function (ThemeRepository $themeRepository) {
                    return $themeRepository->createDisplayabledQueryBuilder();
                },
            ])
        ;

        if ($options['extended']) {
            $builder
                ->add('isInMyTeam', ChoiceType::class, [
                    'choices' => [
                        'Oui' => 1,
                        'Non' => 2,
                        'Déjà pris dans une autre ville' => 3,
                    ],
                    'placeholder' => 'common.all',
                    'required' => false,
                ])
                ->add('tag', TagType::class, [
                    'multiple' => false,
                    'placeholder' => 'common.all',
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ListFilter::class,
                'extended' => false,
            ])
            ->setAllowedTypes('extended', 'bool')
        ;
    }
}
