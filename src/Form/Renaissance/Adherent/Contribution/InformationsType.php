<?php

declare(strict_types=1);

namespace App\Form\Renaissance\Adherent\Contribution;

use App\Adherent\Contribution\ContributionRequest;
use App\Form\ReCountryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountName', TextType::class)
            ->add('accountCountry', ReCountryType::class)
            ->add('iban', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ContributionRequest::class,
                'validation_groups' => ['fill_contribution_informations'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_contribution';
    }
}
