<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setDataMapper($this)
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('zip_code', TextType::class)
            ->add('country', CountryType::class, $this->getCountryOptions($options))
            ->add('accept_email_notifications', CheckboxType::class, [
                'required' => false,
            ])
        ;

        if (in_array('registration', $options['validation_groups'], true)) {
            $builder
                ->add('email_address', RepeatedType::class, [
                    'type' => EmailType::class,
                ])
                ->add('password', PasswordType::class)
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
            'recaptcha' => true,
            'validation_groups' => ['registration'],
            'error_mapping' => [
                'firstName' => 'first_name',
                'lastName' => 'last_name',
                'zipCode' => 'zip_code',
                'emailAddress' => 'email_address',
                'acceptEmailNotifications' => 'accept_email_notifications',
            ],
            'empty_data' => function (FormInterface $form) {
                return new Adherent(
                    Uuid::uuid4(),
                    $form->get('email_address')->getData() ?: '',
                    (string) $form->get('password')->getData(),
                    $form->get('first_name')->getData() ?: '',
                    $form->get('last_name')->getData() ?: '',
                    $form->get('zip_code')->getData() ?: '',
                    $form->get('country')->getData() ?: '',
                    $form->get('accept_email_notifications')->getData()
                );
            },
            'country_iso' => null,
        ]);
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);

        /* @var User $data */
        $forms['first_name']->setData($data ? $data->getFirstName() : '');
        $forms['last_name']->setData($data ? $data->getLastName() : '');
        $forms['zip_code']->setData($data ? $data->getZipCode() : '');
        $forms['country']->setData($data ? $data->getCountry() : '');
        $forms['accept_email_notifications']->setData($data ? $data->getAcceptEmailNotifications() : false);

        !isset($forms['email_address']) ?: $forms['email_address']->setData($data ? $data->getEmailAddress() : '');
        !isset($forms['password']) ?: $forms['password']->setData($data ? $data->getPassword() : '');
    }

    public function mapFormsToData($forms, &$data)
    {
        /** @var User $data */
        $forms = iterator_to_array($forms);

        isset($forms['email_address']) ? $data->setEmailAddress($forms['email_address']->getData() ?? $data->getEmailAddress()) : null;
        isset($forms['password']) ? $data->setPassword($forms['password']->getData() ?? $data->getPassword()) : null;
        $data->setFirstName($forms['first_name']->getData() ?? $data->getFirstName());
        $data->setLastName($forms['last_name']->getData() ?? $data->getLastName());
        $data->setZipCode($forms['zip_code']->getData() ?? $data->getZipCode());
        $data->setCountry($forms['country']->getData() ?? $data->getCountry());
        $data->setAcceptEmailNotifications($forms['accept_email_notifications']->getData() ?? $data->getAcceptEmailNotifications());
    }

    private function getCountryOptions(array $options): array
    {
        $countryOptions = [];

        if (!empty($options['country_iso'])) {
            $countryOptions['data'] = $options['country_iso'];
        }

        return $countryOptions;
    }
}
