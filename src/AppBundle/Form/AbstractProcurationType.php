<?php

namespace AppBundle\Form;

use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProcurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', GenderType::class)
            ->add('lastName', TextType::class)
            ->add('firstNames', TextType::class)
            ->add('country', UnitedNationsCountryType::class)
            ->add('postalCode', TextType::class, [
                'required' => false,
            ])
            ->add('city', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('cityName', TextType::class, [
                'required' => false,
            ])
            ->add('address', TextType::class)
            ->add('phone', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('emailAddress', EmailType::class, [
                'empty_data' => '',
            ])
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'choice',
                'years' => $options['years'],
                'placeholder' => [
                    'year' => 'AAAA',
                    'month' => 'MM',
                    'day' => 'JJ',
                ],
            ])
            ->add('state', HiddenType::class, [
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $request = $event->getData();
        if (!is_array($request)) {
            throw new UnexpectedTypeException($request, 'array');
        }

        if (!in_array($request['state'], ['new_request', 'must_confirm', 'confirmed'])) {
            throw new RuntimeException('Invalid value for the "state" field.');
        }

        // Skip if email field is not set.
        // Validation system must require it first.
        $emailAddress = trim($request['emailAddress']);
        if (empty($emailAddress)) {
            return;
        }

        // In case of one or more existing procurations with the same email
        // address, the user has to resend the form as a confirmation.
        if ('must_confirm' === $request['state']) {
            $request['state'] = 'confirmed';
            $event->setData($request);

            return;
        }

        if ('confirmed' === $request['state']) {
            return;
        }

        // In case of a new procuration request, we must determine whether or
        // not there are already existing procuration requests with the same
        // email address. If yes, the user will have to confirm his choice.
        if ('new_request' === $request['state']) {
            if ($this->matchEmailAddress($emailAddress)) {
                $request['state'] = 'must_confirm';
                $form = $event->getForm();
                $options = $form->getConfig()->getOptions();
                // Force the form error to make the form invalid even though it's not a true error.
                $form->addError(new FormError($options['procuration_unique_message'], $options['procuration_unique_message']));
            } else {
                $request['state'] = 'confirmed';
            }

            $event->setData($request);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        // Make the global "procuration.*.unique" error message just a
        // warning. This way, the template renders it in a different manner
        // than regular global errors.
        $view->vars['warnings'] = [];
        foreach ($errors = iterator_to_array($view->vars['errors']) as $k => $error) {
            if ($options['procuration_unique_message'] === $error->getMessageTemplate()) {
                unset($errors[$k]);
                $view->vars['warnings'][] = $error;
            }
        }

        $view->vars['errors'] = new FormErrorIterator($form, $errors);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $years = range((int) date('Y') - 17, (int) date('Y') - 120);

        $resolver->setDefaults([
            'translation_domain' => false,
            'years' => array_combine($years, $years),
            'procuration_unique_message' => null,
        ]);

        $resolver->setRequired('procuration_unique_message');
    }

    abstract protected function matchEmailAddress(string $emailAddress): bool;
}
