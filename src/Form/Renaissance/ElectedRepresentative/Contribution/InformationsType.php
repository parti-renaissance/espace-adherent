<?php

namespace App\Form\Renaissance\ElectedRepresentative\Contribution;

use App\Address\Address;
use App\ElectedRepresentative\Contribution\ContributionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountName', TextType::class)
            ->add('accountCountry', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('iban', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
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
        return 'app_renaissance_elected_representative_contribution';
    }
}
