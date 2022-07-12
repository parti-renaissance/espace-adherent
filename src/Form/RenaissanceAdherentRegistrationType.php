<?php

namespace App\Form;

use App\Address\Address;
use App\DataTransformer\ValueToDuplicatesTransformer;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Validator\Repeated;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RenaissanceAdherentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', AmountType::class)
            ->add('firstName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('lastName', TextType::class, [
                'format_identity_case' => true,
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('emailAddress', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'common.email.repeated',
                'options' => ['constraints' => [new Repeated([
                    'message' => 'common.email.repeated',
                    'groups' => ['Registration', 'Update'],
                ])]],
            ])
            ->add('position', ActivityPositionType::class, [
                'required' => false,
                'placeholder' => 'common.i.am',
            ])
            ->add('gender', GenderType::class)
            ->add('customGender', TextType::class, [
                'required' => false,
            ])
            ->add('birthdate', BirthdateType::class)
            ->add('address', AddressType::class, [
                'set_address_region' => true,
                'label' => false,
                'child_error_bubbling' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => [Address::FRANCE],
            ])
            ->add('password', PasswordType::class)
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowEmailNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allowMobileNotifications', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $emailForm = $builder->get('emailAddress');
        $emailForm->resetViewTransformers()->addViewTransformer(new ValueToDuplicatesTransformer([
            $emailForm->getOption('first_name'),
            $emailForm->getOption('second_name'),
        ]));

        // Use address country for phone by default
        $builder->get('phone')->get('country')
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) {
                if (!$formEvent->getData()) {
                    $formEvent->setData(Address::FRANCE);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RenaissanceMembershipRequest::class,
            'validation_groups' => ['Update', 'Conditions', 'Registration'],
        ]);
    }
}
