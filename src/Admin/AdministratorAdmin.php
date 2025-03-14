<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\AdministratorRole;
use App\Repository\AdministratorRoleRepository;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Service\Attribute\Required;

class AdministratorAdmin extends AbstractAdmin
{
    private UserPasswordHasherInterface $hasher;

    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    private AdministratorRoleHistoryHandler $administratorRoleHistoryHandler;

    private array $rolesBeforeUpdate = [];

    public function __construct(?string $code, ?string $class, ?string $baseControllerName, AdministratorRoleHistoryHandler $administratorRoleHistoryHandler)
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
                    'query_builder' => function (AdministratorRoleRepository $repository) {
                        return $repository
                            ->createQueryBuilder('role')
                            ->andWhere('role.enabled IS TRUE')
                        ;
                    },
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
                    ? $this->hasher->hashPassword($admin, $plain)
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

    #[Required]
    public function setHasher(UserPasswordHasherInterface $hasher): void
    {
        $this->hasher = $hasher;
    }

    public function setGoogleAuthenticator(GoogleAuthenticator $googleAuthenticator): void
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }
}
