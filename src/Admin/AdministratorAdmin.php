<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\AdministratorRole;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\CallbackTransformer;
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

    private AdministratorRoleHistoryHandler $administratorRoleHistoryHandler;

    private array $rolesBeforeUpdate = [];

    public function __construct(string $code = null, string $class = null, string $baseControllerName = null, AdministratorRoleHistoryHandler $administratorRoleHistoryHandler)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->administratorRoleHistoryHandler = $administratorRoleHistoryHandler;
    }

    /**
     * @param Administrator $object
     */
    protected function alterObject(object $object): void
    {
        $this->rolesBeforeUpdate = $object->getAdministratorRoleCodes();
    }

    /**
     * @param Administrator $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $object->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
    }

    /**
     * @param Administrator $object
     */
    protected function postUpdate(object $object): void
    {
        $this->administratorRoleHistoryHandler->handleChanges($object, $this->rolesBeforeUpdate);
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
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('emailAddress', EmailType::class, [
                    'label' => 'Adresse email',
                ])
                ->add('activated', null, [
                    'label' => 'Activé',
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'required' => $isCreation,
                ])
                ->add('googleAuthenticatorSecret', null, [
                    'label' => 'Clé Google Authenticator',
                    'disabled' => $isCreation,
                ])
            ->end()
            ->with('Permissions', ['class' => 'col-md-6'])
                ->add('administratorRoles', null, [
                    'label' => false,
                    'expanded' => true,
                    'group_by' => static function (AdministratorRole $role): string {
                        return $role->groupCode->value;
                    },
                ])
            ->end()
        ;

        $form->getFormBuilder()->get('password')->addModelTransformer(new CallbackTransformer(
            function () { return ''; },
            function ($plain) use ($admin) {
                return \is_string($plain) && '' !== $plain
                    ? $this->encoders->getEncoder($admin)->encodePassword($plain, null)
                    : $admin->getPassword();
            }
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('emailAddress', null, [
                'label' => 'Email',
                'show_filter' => true,
            ])
            ->add('administratorRoles', null, [
                'label' => 'Rôles',
                'show_filter' => true,
                'field_options' => [
                    'choice_label' => function (AdministratorRole $role): string {
                        return $role->label;
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('emailAddress', null, [
                'label' => 'Adresse email',
            ])
            ->add('administratorRoles', null, [
                'label' => 'Rôles',
                'template' => 'admin/admin/list_administrator_roles.html.twig',
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
