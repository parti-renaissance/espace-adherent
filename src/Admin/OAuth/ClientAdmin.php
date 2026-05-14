<?php

declare(strict_types=1);

namespace App\Admin\OAuth;

use App\Admin\AbstractAdmin;
use App\AppCodeEnum;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\AuthorizationCode;
use App\Entity\OAuth\UserAuthorization;
use App\OAuth\Form\GrantTypesType;
use App\OAuth\Form\ScopesType;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ClientAdmin extends AbstractAdmin
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name', null, ['label' => 'Nom']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Nom',
                'route' => [
                    'name' => 'show',
                ],
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('allowedGrantTypes', 'array', [
                'label' => 'Grant Types OAuth2',
                'template' => 'admin/oauth/client/_list_allowedGrantTypes.html.twig',
            ])
            ->add('askUserForAuthorization', null, [
                'label' => 'Demander l\'autorisation de connexion sur cette application',
            ])
            ->add('supportedScopes', 'array', [
                'label' => 'Scopes autorisés',
                'template' => 'admin/oauth/client/_list_scopes.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [
                        'template' => '@SonataAdmin/CRUD/list__action_delete.html.twig',
                    ],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Informations')
                ->add('name', null, ['label' => 'Nom'])
                ->add('code', null, ['label' => 'Code'])
                ->add('description', null, ['label' => 'Description'])
                ->add('redirectUris', 'array', [
                    'label' => 'Adresses de redirection',
                    'template' => 'admin/oauth/client/_show_redirectUris.html.twig',
                ])
                ->add('postLogoutRedirectUris', 'array', [
                    'label' => 'URIs de redirection post-logout (OIDC)',
                ])
                ->add('pkceRequired', 'boolean', [
                    'label' => 'PKCE requis (S256)',
                ])
                ->add('createdAt', 'datetime', ['label' => 'Date de création'])
                ->add('updatedAt', 'datetime', ['label' => 'Date de modification'])
            ->end()
            ->with('Paramètres de connexion')
                ->add('askUserForAuthorization', 'boolean', [
                    'label' => 'Demander l\'autorisation de connexion sur cette application',
                ])
                ->add('allowedGrantTypes', 'array', [
                    'label' => 'Grant Types OAuth2',
                    'template' => 'admin/oauth/client/_show_allowedGrantTypes.html.twig',
                ])
                ->add('supportedScopes', 'array', [
                    'label' => 'Scopes autorisés',
                    'template' => 'admin/oauth/client/_show_scopes.html.twig',
                ])
                ->add('requestedRoles', null, ['label' => 'Rôles utilisateur nécessaire'])
                ->add('uuid', null, ['label' => 'Consumer Key (API Key)'])
                ->add('secret', null, ['label' => 'Consumer Secret (API Secret)'])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', ChoiceType::class, [
                'label' => 'Code',
                'required' => false,
                'choices' => array_combine(AppCodeEnum::toArray(), AppCodeEnum::toArray()),
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('askUserForAuthorization', null, [
                'label' => 'Demander l\'autorisation de connexion sur cette application',
            ])
            ->add('supportedScopes', ScopesType::class, ['error_bubbling' => false])
            ->add('allowedGrantTypes', GrantTypesType::class, ['error_bubbling' => false])
            ->add('redirectUris', CollectionType::class, [
                'label' => 'Adresses de redirection',
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
            ->add('postLogoutRedirectUris', CollectionType::class, [
                'label' => 'URIs de redirection post-logout (OIDC)',
                'required' => false,
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
                'help' => 'URIs autorisées pour le paramètre post_logout_redirect_uri du flow OIDC end-session. Laisser vide pour les clients non-OIDC.',
            ])
            ->add('pkceRequired', null, [
                'label' => 'PKCE requis (S256)',
                'required' => false,
                'help' => 'Force l\'utilisation de PKCE (S256) pour les flows authorization_code de ce client.',
            ])
            ->add('requestedRoles', CollectionType::class, [
                'required' => false,
                'label' => 'Rôles utilisateur nécessaire',
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    protected function preRemove(object $object): void
    {
        // Remove dependent records through the UoW so they are deleted in the same flush as the Client.
        // Refresh tokens cascade automatically via FK ON DELETE CASCADE on access_token_id.
        $dependentRepositories = [AccessToken::class, AuthorizationCode::class, UserAuthorization::class];
        foreach ($dependentRepositories as $class) {
            foreach ($this->entityManager->getRepository($class)->findBy(['client' => $object]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }
    }
}
