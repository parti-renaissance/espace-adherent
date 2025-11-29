<?php

declare(strict_types=1);

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Form\CroppedImageType;
use App\Form\DoubleNewlineTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCandidacyBiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', CroppedImageType::class, [
                'required' => false,
                'label' => false,
                'image_path' => $options['image_path'],
            ])
            ->add('biography', DoubleNewlineTextareaType::class, [
                'required' => false,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BaseCandidacy::class,
            'image_path' => null,
        ])
            ->setAllowedTypes('image_path', ['string', 'null'])
        ;
    }
}
