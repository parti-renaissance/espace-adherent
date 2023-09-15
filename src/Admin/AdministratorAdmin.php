<?php

namespace App\Admin;

use App\Entity\Administrator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
     * @param Administrator $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $object->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Administrator $admin */
        $admin = $this->getSubject();
        $isCreation = null === $admin->getId();

        $form
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('activated', null, [
                'label' => 'Activé',
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
                    'ROLE_ADMIN_MY_EUROPE',
                    'ROLE_ADMIN_LEGISLATIVES',
                    'ROLE_ADMIN_ADHERENTS',
                    'ROLE_ADMIN_ADHERENT_ELECTED_REPRESENTATIVES',
                    'ROLE_ADMIN_ADHERENTS_READONLY',
                    'ROLE_ADMIN_COMMITTEES',
                    'ROLE_ADMIN_COMMITTEES_MERGE',
                    'ROLE_ADMIN_COMMITTEE_DESIGNATION',
                    'ROLE_ADMIN_EVENTS',
                    'ROLE_ADMIN_REPORTS',
                    'ROLE_ADMIN_PROCURATIONS',
                    'ROLE_ADMIN_ELECTIONS',
                    'ROLE_ADMIN_DONATIONS',
                    'ROLE_ADMIN_MAILER',
                    'ROLE_ADMIN_ADHERENT_TAGS',
                    'ROLE_ADMIN_REFERENT_TAGS',
                    'ROLE_ADMIN_REFERENTS',
                    'ROLE_ADMIN_REFERENTS_AREAS',
                    'ROLE_ADMIN_BOARD_MEMBER_ROLES',
                    'ROLE_ADMIN_TIMELINE',
                    'ROLE_ADMIN_CLIENTS',
                    'ROLE_ADMIN_SCOPES',
                    'ROLE_ADMIN_ORGANIGRAMM',
                    'ROLE_ADMIN_MOOC',
                    'ROLE_ADMIN_EMAIL',
                    'ROLE_ADMIN_EMAIL_SUBSCRIPTION_TYPES',
                    'ROLE_ADMIN_LABEL',
                    'ROLE_ADMIN_BIOGRAPHY',
                    'ROLE_ADMIN_JECOUTE',
                    'ROLE_ADMIN_BAN',
                    'ROLE_ADMIN_CERTIFICATION',
                    'ROLE_ADMIN_FORMATION',
                    'ROLE_ADMIN_ASSESSOR',
                    'ROLE_ADMIN_APPLICATION_REQUEST',
                    'ROLE_ADMIN_CHEZ_VOUS',
                    'ROLE_ADMIN_FINANCE',
                    'ROLE_ADMIN_CREATE_RENAISSANCE_ADHERENT',
                    'ROLE_ADMIN_PROGRAMMATIC_FOUNDATION',
                    'ROLE_ADMIN_ELECTION_CITY_CARD',
                    'ROLE_ADMIN_ELECTION_CITY_CARD_MANAGERS',
                    'ROLE_ADMIN_ELECTED_REPRESENTATIVE',
                    'ROLE_APP_ADMIN_ELECTED_REPRESENTATIVE_LABELS',
                    'ROLE_ADMIN_TERRITORIAL_COUNCIL',
                    'ROLE_ADMIN_TERRITORIAL_COUNCIL_MEMBERSHIP_LOG',
                    'ROLE_ADMIN_THEMATIC_COMMUNITY',
                    'ROLE_ADMIN_FILES',
                    'ROLE_APP_ADMIN_ADHERENT_CONSEIL',
                    'ROLE_ADMIN_TEAMS',
                    'ROLE_ADMIN_PHONING_CAMPAIGNS',
                    'ROLE_ADMIN_PAP_CAMPAIGNS',
                    'ROLE_ADMIN_SMS_CAMPAIGNS',
                    'ROLE_ADMIN_QR_CODES',
                    'ROLE_ADMIN_CMS_BLOCKS',
                    'ROLE_ADMIN_JEMENGAGE_COM',
                    'ROLE_ADMIN_RENAISSANCE',
                    'ROLE_ADMIN_LOCAL_ELECTION',
                    'ROLE_ADMIN_JME_DOCUMENTS',
                    'ROLE_ADMIN_JME_GENERAL_MEETING_REPORT',
                    'ROLE_ADMIN_JME_EMAIL_TEMPLATE',
                    'ROLE_ADMIN_ELUS_NOTIFICATION',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => $isCreation,
            ])
        ;

        $form->getFormBuilder()->get('password')->addModelTransformer(new CallbackTransformer(
            function () { return ''; },
            function ($plain) use ($admin) {
                return \is_string($plain) && '' !== $plain
                ? $this->encoders->getEncoder($admin)->encodePassword($plain, null)
                : $admin->getPassword();
            }
        ));

        if (!$isCreation) {
            $form->add('googleAuthenticatorSecret', null, [
                'label' => 'Clé Google Authenticator',
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('emailAddress', null, [
                'label' => 'E-mail',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'qrcode' => [
                        'template' => 'admin/admin/list_qrcode.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
            ->add('activated', null, [
                'label' => 'Activé',
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('delete');
    }

    public function setEncoders(EncoderFactoryInterface $encoders): void
    {
        $this->encoders = $encoders;
    }

    public function setGoogleAuthenticator(GoogleAuthenticator $googleAuthenticator): void
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }
}
