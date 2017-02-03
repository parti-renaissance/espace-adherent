<?php

namespace AppBundle\Form;

use AppBundle\Donation\DonationRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonationRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', NumberType::class);

        if ($options['sponsor_form']) {
            $builder
                ->add('gender', GenderType::class)
                ->add('firstName', TextType::class)
                ->add('lastName', TextType::class)
                ->add('emailAddress', EmailType::class, [
                    'empty_data' => '',
                ])
                ->add('address', AddressType::class)
                ->add('phone', PhoneNumberType::class, [
                    'required' => false,
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
            ;
        }

        if ($options['submit_button']) {
            $builder->add('submit', SubmitType::class, [
                'label' => $options['submit_label'] ?? 'Je donne',
            ]);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['sponsor_form']) {
            $view->vars['sponsor_form'] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'locale' => 'fr',
                'sponsor_form' => true,
                'submit_button' => true,
                'data_class' => DonationRequest::class,
                'translation_domain' => false,
            ])
            ->setDefined('submit_label')
            ->setAllowedTypes('locale', ['null', 'string'])
            ->setAllowedTypes('sponsor_form', 'bool')
            ->setAllowedTypes('submit_button', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_donation';
    }
}
