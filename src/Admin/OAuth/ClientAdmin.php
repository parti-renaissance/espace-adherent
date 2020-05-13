<?php

namespace App\Admin\OAuth;

use App\Form\WebHookType;
use App\OAuth\ClientManager;
use App\OAuth\Form\GrantTypesType;
use App\OAuth\Form\ScopesType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ClientAdmin extends AbstractAdmin
{
    /**
     * @var ClientManager
     */
    private $clientManager;

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();

        $query->andWhere(
            $query->expr()->isNull($query->getRootAliases()[0].'.deletedAt')
        );

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
            ->add('webHooks', 'array', [
                'label' => 'Web hooks',
                'template' => 'admin/oauth/client/_list_webHooks.html.twig',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [
                        'template' => '@SonataAdmin/CRUD/list__action_delete.html.twig',
                    ],
                ],
            ])
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations')
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                ])
                ->add('redirectUris', 'array', [
                    'label' => 'Adresses de redirection',
                    'template' => 'admin/oauth/client/_show_redirectUris.html.twig',
                ])
                ->add('createdAt', 'datetime', [
                    'label' => 'Date de création',
                ])
                ->add('updatedAt', 'datetime', [
                    'label' => 'Date de modification',
                ])
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
                ->add('uuid', null, [
                    'label' => 'Consumer Key (API Key)',
                ])
                ->add('secret', null, [
                    'label' => 'Consumer Secret (API Secret)',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, [
                'label' => 'Nom',
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
            ->add('webHooks', CollectionType::class, [
                'label' => 'Web hooks',
                'entry_type' => WebHookType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;
    }

    public function delete($object)
    {
        $this->clientManager->delete($object);
    }

    public function setClientManager(ClientManager $clientManager): void
    {
        $this->clientManager = $clientManager;
    }

    protected function configureBatchActions($actions)
    {
        return [];
    }
}
