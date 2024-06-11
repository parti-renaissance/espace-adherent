<?php

namespace App\Form\Procuration\V2;

use App\Address\AddressInterface;
use App\Entity\ProcurationV2\Election;
use App\Entity\ProcurationV2\Round;
use App\Form\AcceptPersonalDataCollectType;
use App\Form\AutocompleteAddressType;
use App\Form\BirthdateType;
use App\Form\CivilityType;
use App\Form\ZoneUuidType;
use Doctrine\ORM\EntityRepository;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProcurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $election = $options['election'];

        $builder
            ->add('rounds', EntityType::class, [
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'class' => Round::class,
                'query_builder' => function (EntityRepository $er) use ($election) {
                    return $er->createQueryBuilder('round')
                        ->where('round.election = :election')
                        ->setParameter('election', $election)
                    ;
                },
                'choice_label' => function (Round $round): string {
                    return $round->name;
                },
            ])
            ->add('email', TextType::class)
            ->add('acceptCgu', AcceptPersonalDataCollectType::class, [
                'mapped' => false,
            ])
            ->add('gender', CivilityType::class)
            ->add('firstNames', TextType::class)
            ->add('lastName', TextType::class)
            ->add('birthdate', BirthdateType::class)
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [AddressInterface::FRANCE],
                'default_region' => AddressInterface::FRANCE,
                'country_display_type' => PhoneNumberType::DISPLAY_COUNTRY_SHORT,
            ])
            ->add('address', AutocompleteAddressType::class, [
                'with_additional_address' => true,
            ])
            ->add('distantVotePlace', CheckboxType::class, [
                'required' => false,
            ])
            ->add('voteZone', ZoneUuidType::class)
            ->add('votePlace', ZoneUuidType::class, [
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('customVotePlace', TextType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('joinNewsletter', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'election' => null,
            ])
            ->setRequired('election')
            ->setAllowedTypes('election', Election::class)
        ;
    }
}
