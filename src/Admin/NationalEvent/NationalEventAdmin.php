<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent;

use App\Admin\AbstractAdmin;
use App\Form\Admin\UploadableFileType;
use App\Form\CkEditorType;
use App\Form\ColorType;
use App\Form\JsonType;
use App\NationalEvent\NationalEventTypeEnum;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class NationalEventAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('inscriptions', $this->getRouterIdParameter().'/inscriptions')
            ->add('sendTickets', $this->getRouterIdParameter().'/send-tickets')
            ->add('sendPush', $this->getRouterIdParameter().'/send-push')
            ->add('generateTicketQRCodes', $this->getRouterIdParameter().'/generate-ticket-qrcodes')
        ;
    }

    protected function getAccessMapping(): array
    {
        return [
            'inscriptions' => AdminPermissionMap::PERMISSION_EDIT,
            'sendTickets' => AdminPermissionMap::PERMISSION_EDIT,
            'sendPush' => AdminPermissionMap::PERMISSION_EDIT,
            'generateTicketQRCodes' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, ['label' => 'Nom'])
            ->add('type', 'enum', ['label' => 'Type', 'use_value' => true, 'enum_translation_domain' => 'messages'])
            ->add('startDate', null, ['label' => 'Date de début'])
            ->add('endDate', null, ['label' => 'Date de fin'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
                'edit' => [],
                'inscriptions' => ['template' => 'admin/national_event/list_action_inscriptions.html.twig'],
                'link' => ['template' => 'admin/national_event/list_action_link.html.twig'],
            ]])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('updatedAt', null, ['label' => 'Date de modification'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Général 📜')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('name', null, ['label' => 'Nom'])
                    ->add('type', ChoiceType::class, [
                        'label' => 'Type',
                        'help' => 'Définit le template utilisé pour la page d\'inscription',
                        'choices' => NationalEventTypeEnum::all(),
                        'choice_label' => fn (NationalEventTypeEnum $type) => $type->trans($this->getTranslator()),
                    ])
                    ->add('source', null, ['label' => 'Source', 'help' => 'UTM source pour s\'inscrire même après la fermeture des inscriptions'])
                ->end()
                ->with('Dates', ['class' => 'col-md-6'])
                    ->add('ticketStartDate', null, ['label' => 'Billetterie : date de début', 'widget' => 'single_text', 'with_seconds' => true])
                    ->add('ticketEndDate', null, ['label' => 'Billetterie : date de fin', 'widget' => 'single_text', 'with_seconds' => true])
                    ->add('startDate', null, ['label' => 'Meeting : date de début', 'widget' => 'single_text', 'with_seconds' => true])
                    ->add('endDate', null, ['label' => 'Meeting : date de fin', 'widget' => 'single_text', 'with_seconds' => true])
                    ->add('inscriptionEditDeadline', null, ['label' => 'Date limite de modification de l\'inscription', 'widget' => 'single_text', 'with_seconds' => false])
                ->end()
            ->end()
            ->tab('Images 🖼️')
                ->with('Couverture', ['class' => 'col-md-6', 'description' => 'Image de couverture affichée en haut de la page d\'inscription'])
                    ->add('intoImage', UploadableFileType::class, ['label' => false, 'required' => false])
                ->end()
                ->with('Alerte & OG Image', ['class' => 'col-md-6', 'description' => 'Image affichée dans l\'alerte et dans les balises Open Graph'])
                    ->add('ogImage', UploadableFileType::class, ['label' => false])
                ->end()
                ->with('Logo', ['class' => 'col-md-6', 'description' => 'Logo affiché dans l\'alerte'])
                    ->add('logoImage', UploadableFileType::class, ['label' => false])
                ->end()
            ->end()
            ->tab('Wording 📝')
                ->with('Introduction', ['class' => 'col-md-6', 'description' => 'Texte d\'introduction affiché en haut de la page d\'inscription'])
                    ->add('textIntro', CkEditorType::class, ['label' => false])
                ->end()
                ->with('Message d\'aide', ['class' => 'col-md-6', 'description' => 'Texte d\'aide affiché en bas de la page d\'inscription, avant le bouton de Légalités'])
                    ->add('textHelp', CkEditorType::class, ['label' => false])
                ->end()
                ->with('Message de confirmation', ['class' => 'col-md-6', 'description' => 'Texte affiché sur la page de confirmation et dans le mail de confirmation'])
                    ->add('textConfirmation', CkEditorType::class, ['label' => false])
                ->end()
            ->end()
            ->tab('Alerte & OG 📯')
                ->with('Alerte', ['class' => 'col-md-6'])
                    ->add('alertEnabled', null, ['label' => 'Alerte activée', 'required' => false])
                    ->add('alertTitle', TextType::class, ['label' => 'Titre', 'required' => false])
                    ->add('alertDescription', TextType::class, ['label' => 'Description', 'required' => false])
                    ->add('alertLogoImage', UploadableFileType::class, ['label' => 'Logo de l\'alerte', 'required' => false])
                ->end()
                ->with('Open Graph', ['class' => 'col-md-6'])
                    ->add('ogTitle', TextType::class, ['label' => 'Titre', 'required' => false])
                    ->add('ogDescription', TextType::class, ['label' => 'Description', 'required' => false])
                ->end()
            ->end()
            ->tab('Ticket 🎟️')
                ->with('Contenu du mail de billet', ['class' => 'col-md-6'])
                    ->add('subjectTicketEmail', TextType::class, ['label' => 'Objet', 'required' => true])
                    ->add('imageTicketEmail', UrlType::class, ['label' => 'URL de l\'image', 'required' => true])
                    ->add('textTicketEmail', CkEditorType::class, ['label' => 'Détail', 'required' => true])
                ->end()
                ->with('Options pour les inscriptions', ['class' => 'col-md-6', 'description' => 'Options appliquées par défaut aux nouvelles inscriptions.'])
                    ->add('defaultAccess', null, ['label' => 'Porte par défaut', 'required' => false])
                    ->add('defaultBracelet', null, ['label' => 'Bracelet par défaut', 'required' => false])
                    ->add('defaultBraceletColor', ColorType::class, ['label' => 'Couleur bracelet par défaut', 'required' => false])
                ->end()
            ->end()
            ->tab('Configuration forfaits  🛠️')
                ->with('', ['class' => 'col-md-12'])
                    ->add('packageConfig', JsonType::class, ['label' => false, 'required' => false, 'attr' => ['rows' => 25], 'help' => 'Configuration des transports et tarifs. Utilisez le format JSON.'])
                ->end()
            ->end()
            ->tab('Autre configuration  ⚙️')
                ->with('Connection & Synchronisation', ['class' => 'col-md-6'])
                    ->add('connectionEnabled', null, ['label' => 'Activer la connexion militant', 'required' => false])
                    ->add('mailchimpSync', null, ['label' => 'Synchronisation des participants vers Mailchimp', 'required' => false])
                ->end()
                ->with('Réduction', ['class' => 'col-md-6'])
                    ->add('discountLabel', null, ['label' => 'Libellé de la case à cocher de la réduction', 'required' => false])
                    ->add('discountHelp', TextareaType::class, [
                        'label' => 'Le text descriptif de la réduction (optionnel, HTML acceptable)',
                        'required' => false,
                        'attr' => ['rows' => 7],
                    ])
                ->end()
            ->end()
        ;
    }
}
