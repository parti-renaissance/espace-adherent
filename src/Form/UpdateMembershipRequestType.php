<?php

namespace AppBundle\Form;

use AppBundle\Membership\MembershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

class UpdateMembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class)
            ->add('firstName', TextType::class, ['disabled' => $options['name_and_email_fields_disabled']])
            ->add('lastName', TextType::class, ['disabled' => $options['name_and_email_fields_disabled']])
            ->add('emailAddress', EmailType::class, ['disabled' => $options['name_and_email_fields_disabled']])
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'choice',
                'years' => $options['years'],
                'placeholder' => [
                    'year' => 'AAAA',
                    'month' => 'MM',
                    'day' => 'JJ',
                ],
            ])
            ->add('position', ActivityPositionType::class)
            ->add('address', AddressType::class)
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('comMobile', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y') - 15, (int) date('Y') - 120);

        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'years' => array_combine($years, $years),
            'validation_groups' => ['Update'],
            'name_and_email_fields_disabled' => true,
        ]);

        $resolver->setAllowedTypes('name_and_email_fields_disabled', 'bool');
    }
}
