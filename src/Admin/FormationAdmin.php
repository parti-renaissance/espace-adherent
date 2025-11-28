<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\AdherentFormation\Formation;
use App\Entity\AdherentFormation\FormationContentTypeEnum;
use App\Form\PositionType;
use App\Formation\FormationHandler;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class FormationAdmin extends AbstractAdmin
{
    public function __construct(private readonly FormationHandler $formationHandler)
    {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Optionnelle. Sera affichée aux utilisateurs',
                ])
                ->add('category', TextType::class, [
                    'label' => 'Catégorie',
                    'required' => false,
                ])
            ->end()
            ->with('Visibilité', ['class' => 'col-md-6'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publiée ?',
                    'required' => false,
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'label' => 'Zone',
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                ])
                ->add('position', PositionType::class, [
                    'label' => 'Position sur la page',
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-6'])
                ->add('contentType', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => FormationContentTypeEnum::ALL,
                    'choice_label' => function (string $choice): string {
                        return \sprintf('adherent_formation.content_type.%s', $choice);
                    },
                ])
                ->add('file', FileType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('link', UrlType::class, [
                    'label' => 'Lien',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://',
                    ],
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
                'show_filter' => true,
            ])
            ->add('published', null, [
                'label' => 'Publiée',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('printCount', null, [
                'label' => 'Nb de téléchargements',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'download' => [
                        'template' => 'admin/formation/_action_download.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param Formation $object
     */
    protected function prePersist(object $object): void
    {
        $this->handleFile($object);
    }

    /**
     * @param Formation $object
     */
    protected function preUpdate(object $object): void
    {
        $this->handleFile($object);
    }

    private function handleFile(Formation $formation): void
    {
        $this->formationHandler->handleFile($formation);
    }
}
