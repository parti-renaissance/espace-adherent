<?php

namespace App\Form\Jecoute;

use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $region = $builder->getData();
        $builder
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('description', TextareaType::class, [
                'filter_emojis' => true,
                'attr' => ['rows' => 20],
            ])
            ->add('primaryColor', ChoiceType::class, [
                'choices' => RegionColorEnum::all(),
                'choice_label' => function (string $choice) {
                    return "common.$choice";
                },
            ])
            ->add('logoFile', FileType::class, [
                'required' => !$region->hasLogoUploaded(),
                'attr' => ['accept' => 'image/*'],
                'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
            ])
            ->add('bannerFile', FileType::class, [
                'required' => false,
                'attr' => ['accept' => 'image/*'],
                'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
            ])
            ->add('removeBanner', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Region::class,
            ])
        ;
    }
}
