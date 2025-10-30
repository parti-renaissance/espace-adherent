<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\AdministratorRole;
use App\Entity\Geo\Zone;
use App\Repository\AdministratorRoleRepository;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdministratorAdmin extends AbstractAdmin
{
    private array $rolesBeforeUpdate = [];

    public function __construct(
        private readonly AdministratorRoleHistoryHandler $administratorRoleHistoryHandler,
        private readonly GoogleAuthenticator $googleAuthenticator,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
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

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Administrator $admin */
        $admin = $this->getSubject();
        $isCreation = $this->isCreation();

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
            ->with('Périmètre géographique', ['class' => 'col-md-6'])
                ->add('zones', ModelAutocompleteType::class, [
                    'callback' => [$this, 'prepareAutocompleteFilterCallback'],
                    'multiple' => true,
                    'property' => ['name', 'code'],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'btn_add' => false,
                    'label' => false,
                ])
            ->end()
            ->with('Permissions')
                ->add('administratorRoles', null, [
                    'label' => false,
                    'expanded' => true,
                    'query_builder' => function (AdministratorRoleRepository $repository) {
                        return $repository
                            ->createQueryBuilder('role')
                            ->andWhere('role.enabled = 1')
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
            ->add('activated', null, [
                'label' => 'Activé',
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
            ->add('zones', null, [
                'label' => 'Zones géographiques',
                'template' => 'admin/scope/list_zones.html.twig',
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

    public static function prepareAutocompleteFilterCallback(
        SonataAbstractAdmin $admin,
        array $properties,
        string $value,
    ): void {
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();

        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }

        $qb
            ->andWhere($orx)
            ->andWhere($alias.'.type = :dpt_type')
            ->setParameter('dpt_type', Zone::DEPARTMENT)
        ;
    }
}
