<?php

declare(strict_types=1);

namespace App\Form\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use App\Form\CroppedImageType;
use App\Jecoute\RegionColorEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $region = $builder->getData();
        $hasMultiZone = $options['has_multi_zone'];

        if ($hasMultiZone) {
            $builder
                ->add('zone', EntityType::class, [
                    'class' => Zone::class,
                    'choices' => $options['zones'],
                ])
            ;
        }

        $builder
            ->add('subtitle', TextType::class)
            ->add('description', TextareaType::class, [
                'attr' => ['rows' => 20],
            ])
            ->add('primaryColor', ChoiceType::class, [
                'choices' => RegionColorEnum::all(),
                'choice_label' => function (string $choice) {
                    return "common.$choice";
                },
            ])
            ->add('externalLink', UrlType::class, [
                'required' => false,
            ])
            ->add('logoFile', CroppedImageType::class, [
                'attr' => ['accept' => 'image/*'],
                'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                'label' => 'Logo',
                'image_path' => $region->hasLogoUploaded() ? $region->getLogoPathWithDirectory() : null,
            ])
            ->add('bannerFile', CroppedImageType::class, [
                'required' => false,
                'attr' => ['accept' => 'image/*'],
                'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                'label' => 'Bannière',
                'image_path' => $region->hasBannerUploaded() ? $region->getBannerPathWithDirectory() : null,
                'ratio' => CroppedImageType::RATIO_16_9,
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Personnalisation active',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['zones', 'has_multi_zone'])
            ->setAllowedTypes('zones', [Zone::class.'[]'])
            ->setAllowedTypes('has_multi_zone', 'bool')
            ->setDefaults([
                'data_class' => Region::class,
                'zones' => [],
                'has_multi_zone' => false,
            ])
        ;
    }
}
