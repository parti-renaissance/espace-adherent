<?php

declare(strict_types=1);

namespace App\Admin\Renaissance;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\String\Slugger\AsciiSlugger;

class NewsletterSourceAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'create', 'edit', 'delete']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, ['label' => 'Code', 'show_filter' => true])
            ->add('label', null, ['label' => 'Libellé', 'show_filter' => true])
            ->add('enabled', null, ['label' => 'Actif', 'show_filter' => true])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('code', null, ['label' => 'Code'])
            ->add('label', null, ['label' => 'Libellé'])
            ->add('enabled', null, ['label' => 'Actif', 'editable' => true])
            ->add('mailchimpTag', null, ['label' => 'Tag Mailchimp'])
            ->add('confirmationRedirectUrl', null, ['label' => 'URL de redirection après confirmation'])
            ->add('updatedAt', null, ['label' => 'Dernière modification'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Source de newsletter Renaissance', ['class' => 'col-md-8'])
                ->add('code', TextType::class, [
                    'label' => 'Code',
                    'help' => 'Identifiant technique envoyé par le client API dans le champ "source". À la sauvegarde, la valeur est automatiquement normalisée (minuscules, accents retirés, espaces remplacés par "_"). Exemple : "Site EU 2026" → "site_eu_2026".',
                ])
                ->add('label', TextType::class, [
                    'label' => 'Libellé',
                    'help' => 'Nom lisible pour le back-office uniquement.',
                ])
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Source active',
                    'required' => false,
                    'help' => 'Si décoché, les nouvelles inscriptions avec ce code seront rejetées (400). Les inscriptions existantes restent confirmables.',
                ])
                ->add('mailchimpTag', TextType::class, [
                    'label' => 'Tag Mailchimp',
                    'required' => false,
                    'help' => 'Tag appliqué sur le membre Mailchimp lors de la sync. Si vide, aucun tag n\'est ajouté. Peut contenir des espaces et des accents (ex. "Europe 2026"). ⚠ Une modification ne s\'applique qu\'aux inscriptions synchronisées après le changement — les inscriptions déjà synchronisées conservent leur tag initial côté Mailchimp.',
                ])
                ->add('confirmationRedirectUrl', UrlType::class, [
                    'label' => 'URL de redirection après confirmation',
                    'required' => false,
                    'default_protocol' => 'https',
                    'help' => 'HTTPS uniquement. Si vide, redirection par défaut vers la page adhérent.',
                ])
            ->end()
        ;

        $slugger = new AsciiSlugger();
        $form->get('code')->addModelTransformer(new CallbackTransformer(
            static fn (?string $stored) => $stored,
            static fn (?string $input) => $input ? (string) $slugger->slug($input, '_')->lower() : null,
        ));
    }
}
