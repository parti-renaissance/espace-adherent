<?php

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
    private FormationHandler $formationHandler;

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Optionnelle. Sera affichée aux utilisateurs',
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
                        return sprintf('adherent_formation.content_type.%s', $choice);
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('published', null, [
                'label' => 'Publiée',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('printCount', null, [
                'label' => 'Vues',
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
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param Formation $object
     */
    protected function postPersist(object $object): void
    {
        $this->handleFile($object);
    }

    /**
     * @param Formation $object
     */
    protected function postUpdate(object $object): void
    {
        $this->handleFile($object);
    }

    private function handleFile(Formation $formation): void
    {
        $this->formationHandler->handleFile($formation);
    }

    /**
     * @required
     */
    public function setFormationHandler(FormationHandler $formationHandler): void
    {
        $this->formationHandler = $formationHandler;
    }
}
