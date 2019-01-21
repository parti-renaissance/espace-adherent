<?php

namespace AppBundle\Form;

use AppBundle\DataTransformer\ValueToDuplicatesTransformer;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Validator\Repeated;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('position')
            ->add('password', PasswordType::class)
            ->add('conditions', CheckboxType::class, [
                'required' => false,
            ])
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
            ->add('address', AddressType::class, [
                'set_address_region' => true,
                'label' => false,
                'child_error_bubbling' => false,
            ])
        ;

        $emailForm = $builder->get('emailAddress');
        $emailForm->resetViewTransformers()->addViewTransformer(new ValueToDuplicatesTransformer([
            $emailForm->getOption('first_name'),
            $emailForm->getOption('second_name'),
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembershipRequest::class,
            'validation_groups' => ['Update', 'Conditions', 'Registration'],
        ]);
    }

    public function getParent(): string
    {
        return AdherentType::class;
    }
}
