<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\VideoStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VideoAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('relaunch', $this->getRouterIdParameter().'/relaunch');
    }

    protected function getAccessMapping(): array
    {
        return [
            'relaunch' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, ['label' => 'Titre'])
                ->add('status', EnumType::class, [
                    'label' => 'Statut',
                    'class' => VideoStatusEnum::class,
                    'choice_label' => fn (VideoStatusEnum $case) => 'video.status.'.strtolower($case->value),
                    'disabled' => true,
                    'help' => 'Piloté par le pipeline de transcodage.',
                ])
                ->add('duration', IntegerType::class, [
                    'label' => 'Durée (s)',
                    'required' => false,
                    'disabled' => true,
                    'help' => 'Renseigné par le transcodage.',
                ])
                ->add('width', IntegerType::class, [
                    'label' => 'Largeur (px)',
                    'required' => false,
                    'disabled' => true,
                    'help' => 'Renseigné par le transcodage.',
                ])
                ->add('height', IntegerType::class, [
                    'label' => 'Hauteur (px)',
                    'required' => false,
                    'disabled' => true,
                    'help' => 'Renseigné par le transcodage.',
                ])
            ->end()
            ->with('Stockage CDN', ['class' => 'col-md-6'])
                ->add('mediaPath', TextType::class, [
                    'label' => 'Chemin média',
                    'required' => false,
                    'disabled' => true,
                    'help' => 'Renseigné par le pipeline d\'upload (ex: videos/<uuid>).',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('uuid', null, ['label' => 'UUID', 'show_filter' => true])
            ->add('title', null, ['label' => 'Titre', 'show_filter' => true])
            ->add('status', null, ['label' => 'Statut', 'show_filter' => true])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('thumbnail', null, [
                'label' => 'Aperçu',
                'virtual_field' => true,
                'template' => 'admin/video/_list_thumbnail.html.twig',
            ])
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/video/_status_label_list.html.twig',
            ])
            ->add('duration', null, ['label' => 'Durée (s)'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'relaunch' => ['template' => 'admin/video/list_action_relaunch.html.twig'],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Lecteur', ['class' => 'col-md-8'])
                ->add('player', null, [
                    'label' => false,
                    'virtual_field' => true,
                    'template' => 'admin/video/_show_player.html.twig',
                ])
            ->end()
            ->with('Informations', ['class' => 'col-md-4'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('status', null, [
                    'label' => 'Statut',
                    'template' => 'admin/video/_status_label_show.html.twig',
                ])
                ->add('failureReason', null, [
                    'label' => 'Erreur de transcodage',
                    'template' => 'admin/video/_show_failure_reason.html.twig',
                ])
                ->add('duration', null, ['label' => 'Durée (s)'])
                ->add('dimensions', null, [
                    'label' => 'Dimensions',
                    'virtual_field' => true,
                    'template' => 'admin/video/_show_dimensions.html.twig',
                ])
                ->add('createdAt', null, ['label' => 'Créée le'])
                ->add('updatedAt', null, ['label' => 'Mise à jour le'])
            ->end()
            ->with('Endpoint API', ['class' => 'col-md-12'])
                ->add('apiUrl', null, [
                    'label' => false,
                    'virtual_field' => true,
                    'template' => 'admin/video/_show_api_link.html.twig',
                ])
            ->end()
            ->with('Identifiants & stockage CDN', ['class' => 'col-md-12', 'collapsed' => true])
                ->add('uuid', null, ['label' => 'UUID'])
                ->add('mediaPath', null, ['label' => 'Chemin média'])
                ->add('cdnUrls', null, [
                    'label' => 'URLs (calculées)',
                    'virtual_field' => true,
                    'template' => 'admin/video/_show_cdn_urls.html.twig',
                ])
            ->end()
        ;
    }
}
