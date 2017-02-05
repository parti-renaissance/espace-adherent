<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Administrator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorAdmin extends AbstractAdmin
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoders;

    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    /**
     * @param Administrator $admin
     */
    public function prePersist($admin)
    {
        parent::prePersist($admin);

        $admin->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Administrator $admin */
        $admin = $this->getSubject();
        $isCreation = null === $admin->getId();

        $formMapper
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Rédacteur' => 'ROLE_WRITER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Développeur' => 'ROLE_DEVELOPER',
                    'Super-administrateur' => 'ROLE_SUPER_ADMIN',
                ],
            ])
            ->add(
                $formMapper->create('password', RepeatedType::class, [
                    'first_options' => [
                        'label' => 'Mot de passe',
                    ],
                    'second_options' => [
                        'label' => 'Confirmation',
                    ],
                    'type' => PasswordType::class,
                    'required' => $isCreation,
                ])
                    ->addModelTransformer(new CallbackTransformer(
                        function ($encoded) {
                            return ''; // or ****
                        },
                        function ($plain) use ($admin) {
                            return is_string($plain) && '' !== $plain
                                ? $this->encoders->getEncoder($admin)->encodePassword($plain, null)
                                : $admin->getPassword();
                        }
                    ))
            );

        if (!$isCreation) {
            $formMapper->add('googleAuthenticatorSecret', null, [
                'label' => 'Clé Google Authenticator',
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('emailAddress', null, [
                'label' => 'E-mail',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('role', null, [
                'label' => 'Rôle',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'qrcode' => [
                        'template' => 'admin/admin_qrcode.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    public function setEncoders(EncoderFactoryInterface $encoders)
    {
        $this->encoders = $encoders;
    }

    public function setGoogleAuthenticator(GoogleAuthenticator $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }
}
