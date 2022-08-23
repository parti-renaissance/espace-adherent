<?php

namespace App\Form\Renaissance\Adhesion;

use App\Address\Address;
use App\DataTransformer\ValueToDuplicatesTransformer;
use App\Form\AddressType;
use App\Form\BirthdateType;
use App\Form\GenderType;
use App\Renaissance\Membership\MembershipRequestCommand;
use App\Validator\Repeated;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestPersonalInfoType extends AbstractType
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

        $builder->add('fill_personal_info', SubmitType::class, ['label' => 'Étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MembershipRequestCommand::class,
                'validation_groups' => 'fill_personal_info',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
