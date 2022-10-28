<?php

namespace App\Form\Renaissance\Adhesion;

use App\Address\Address;
use App\Entity\Adherent;
use App\Form\CivilityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CompleteProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fromCertifiedAdherent = $options['from_certified_adherent'];

        $builder
            ->add('nationality', CountryType::class, [
                'placeholder' => '',
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('gender', CivilityType::class, [
                'disabled' => $fromCertifiedAdherent,
            ])
            ->add('exclusiveMembership', CheckboxType::class, ['required' => false])
            ->add('territoireProgresMembership', CheckboxType::class, ['required' => false])
            ->add('agirMembership', CheckboxType::class, ['required' => false])
            ->add('frenchTaxResidentConfirmation', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [new Callback(['groups' => ['adhesion_complete_profil'], 'callback' => function ($value, ExecutionContextInterface $context, $payload) {
                    /** @var Adherent $adherent */
                    $adherent = $context->getRoot()->getData();
                    if ($adherent && !$adherent->isFrench() && false === $value) {
                        $context->buildViolation('Cette case est obligatoire')->addViolation();
                    }
                }])],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Adherent::class,
                'validation_groups' => ['adhesion_complete_profil'],
                'from_certified_adherent' => false,
            ])
            ->setAllowedTypes('from_certified_adherent', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
