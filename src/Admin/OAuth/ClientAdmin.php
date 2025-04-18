<?php

namespace App\Admin\OAuth;

use App\Admin\AbstractAdmin;
use App\AppCodeEnum;
use App\OAuth\Form\GrantTypesType;
use App\OAuth\Form\ScopesType;
use App\OAuth\TokenRevocationAuthority;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\Service\Attribute\Required;

class ClientAdmin extends AbstractAdmin
{
    private ?TokenRevocationAuthority $tokenRevocationAuthority = null;

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name', null, ['label' => 'Nom']);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query->andWhere(
            $query->expr()->isNull($query->getRootAliases()[0].'.deletedAt')
        );

        return $query;
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
            ->add('requestedRoles', CollectionType::class, [
                'required' => false,
                'label' => 'Rôles utilisateur nécessaire',
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    protected function postRemove(object $object): void
    {
        $this->tokenRevocationAuthority->revokeClientTokens($object);
    }

    #[Required]
    public function setTokenRevocationAuthority(TokenRevocationAuthority $tokenRevocationAuthority): void
    {
        $this->tokenRevocationAuthority = $tokenRevocationAuthority;
    }
}
