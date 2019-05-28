<?php

namespace AppBundle\Form\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\Theme;
use AppBundle\Form\AddressType;
use AppBundle\Form\UnitedNationsCountryType;
use Doctrine\ORM\EntityRepository;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('emailAddress', EmailType::class)
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('address', AddressType::class, [
                'mapped' => false,
            ])
            ->add('profession', TextType::class)
            ->add('favoriteThemes', EntityType::class, [
                'class' => Theme::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC')
                    ;
                },
                'group_by' => function(Theme $theme) {
                    if ('Autre' !== $theme->getName()) {
                        return "Thèmes";
                    } else {
                        return "Autre";
                    }
                },
            ])
            ->add('customFavoriteTheme', TextType::class, [
                'required' => false,
            ])
            ->add('agreeToLREMValues', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('validation_groups', ['application_request']);
    }
}
