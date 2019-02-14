<?php

namespace AppBundle\Form;

use AppBundle\DataTransformer\ValueToDuplicatesTransformer;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Validator\Repeated;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationType extends AbstractType
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
            ])
            ->add('address', AddressType::class)
            ->add('allowNotifications', CheckboxType::class, [
                'required' => false,
            ])
            ->add('emailAddress', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'common.email.repeated',
                'options' => ['constraints' => [new Repeated([
                    'message' => 'common.email.repeated',
                    'groups' => ['Registration', 'Update'],
                ])]],
            ])
            ->add('password', PasswordType::class)
        ;

        $emailForm = $builder->get('emailAddress');
        $emailForm->resetViewTransformers()->addViewTransformer(new ValueToDuplicatesTransformer([
            $emailForm->getOption('first_name'),
            $emailForm->getOption('second_name'),
        ]));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'validation_groups' => ['Registration'],
            'country_iso' => null,
        ]);
    }
}
