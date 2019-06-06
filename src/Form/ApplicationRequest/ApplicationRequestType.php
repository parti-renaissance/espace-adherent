<?php

namespace AppBundle\Form\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\Theme;
use AppBundle\Form\AddressType;
use Doctrine\ORM\EntityRepository;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('favoriteCities', CollectionType::class, [
                'required' => true,
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('favoriteCities_search', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
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
                'group_by' => function (Theme $theme) {
                    if ('Autre(s)' !== $theme->getName()) {
                        return 'Thèmes';
                    } else {
                        return 'Autre';
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

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var ApplicationRequest $data */
            $data = $event->getData();

            $addressForm = $event->getForm()->get('address');

            $data->setAddress($addressForm->get('address')->getData());
            $data->setPostalCode($addressForm->get('postalCode')->getData());
            $data->setCity($addressForm->get('cityName')->getData());
            $data->setCountry($addressForm->get('country')->getData());
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('validation_groups', ['application_request']);
    }
}
