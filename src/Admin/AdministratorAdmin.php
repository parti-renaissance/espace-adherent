<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Administrator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\ListMapper, Datagrid\DatagridMapper, Form\FormMapper
};
use Symfony\Component\{
    Form\CallbackTransformer, Security\Core\Encoder\EncoderFactoryInterface,
    Form\Extension\Core\Type\ChoiceType, Form\Extension\Core\Type\EmailType,
    Form\Extension\Core\Type\PasswordType ,Form\Extension\Core\Type\RepeatedType
};

class AdministratorAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    /**
     * @var EncoderFactoryInterface
     */
    private $_encoders;

    /**
     * @var GoogleAuthenticator
     */
    private $_googleAuthenticator;

    /**
     * @param Administrator $admin
     */
    public function prePersist($admin)
        : void
    {
        parent::prePersist($admin);

        $admin->setGoogleAuthenticatorSecret($this->_googleAuthenticator->generateSecret());
    }

    protected function configureFormFields(FormMapper $formMapper)
        : void
    {
        /** @var Administrator $admin */
        $admin = $this->getSubject();
        $isCreation = null === $admin->getId();

        $formMapper
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Permissions',
                'expanded' => true,
                'multiple' => true,
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'choices' => [
                    'ROLE_SUPER_ADMIN',
                    'ROLE_ADMIN_DASHBOARD',
                    'ROLE_ADMIN_MEDIAS',
                    'ROLE_ADMIN_CONTENT',
                    'ROLE_ADMIN_HOME',
                    'ROLE_ADMIN_PROPOSALS',
                    'ROLE_ADMIN_ORDERS',
                    'ROLE_ADMIN_FACEBOOK_PROFILES',
                    'ROLE_ADMIN_FACEBOOK_VIDEOS',
                    'ROLE_ADMIN_REDIRECTIONS',
                    'ROLE_ADMIN_NEWSLETTER',
                    'ROLE_ADMIN_JE_MARCHE',
                    'ROLE_ADMIN_UNREGISTRATIONS',
                    'ROLE_ADMIN_TON_MACRON',
                    'ROLE_ADMIN_LEGISLATIVES',
                    'ROLE_ADMIN_ADHERENTS',
                    'ROLE_ADMIN_SUMMARY',
                    'ROLE_ADMIN_COMMITTEES',
                    'ROLE_ADMIN_EVENTS',
                    'ROLE_ADMIN_PROCURATIONS',
                    'ROLE_ADMIN_DONATIONS',
                    'ROLE_ADMIN_MAILJET',
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
                                ? $this->_encoders->getEncoder($admin)->encodePassword($plain, null)
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
        : void
    {
        $datagridMapper
            ->add('emailAddress', null, [
                'label' => 'E-mail',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->addIdentifier('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'qrcode' => [
                        'template' => 'admin/admin/list_qrcode.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    public function setEncoders(EncoderFactoryInterface $encoders)
        : self
    {
        $this->_encoders = $encoders;
        return $this;
    }

    public function setGoogleAuthenticator(GoogleAuthenticator $googleAuthenticator)
        : self
    {
        $this->_googleAuthenticator = $googleAuthenticator;
        return $this;
    }
}
