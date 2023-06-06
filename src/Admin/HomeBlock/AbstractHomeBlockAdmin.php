<?php

namespace App\Admin\HomeBlock;

use App\Admin\Color;
use App\Entity\HomeBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractHomeBlockAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param HomeBlock $object
     */
    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($object->getTitle(), $object->getSubtitle(), $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if (null === $this->getSubject()->getId()) {
            // Creation
            $form
                ->add('position', null, [
                    'label' => 'Position du bloc',
                ])
                ->add('position_name', TextType::class, [
                    'label' => 'Nom du bloc',
                ])
            ;
        }

        $form
            ->add('media', null, [
                'label' => 'Image/Vidéo',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Article' => 'article',
                    'Vidéo' => 'video',
                    'Banner' => 'banner',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
                'required' => false,
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
            ])
            ->add('display_filter', CheckboxType::class, [
                'label' => 'Afficher le filtre',
                'required' => false,
            ])
        ;

        if ('Bannière - Gauche' === $this->getSubject()->getPositionName()) {
            $form
                ->add('display_titles', CheckboxType::class, [
                    'label' => 'Afficher le titre et sous-titre',
                    'required' => false,
                ])
            ;
        }

        if (HomeBlock::TYPE_BANNER === $this->getSubject()->getType()) {
            $form
                ->add('title_cta', TextType::class, [
                    'label' => 'Texte du CTA',
                    'required' => false,
                ])
                ->add('color_cta', ChoiceType::class, [
                    'label' => 'Couleur du CTA',
                    'required' => false,
                    'choices' => Color::CHOICES,
                ])
                ->add('bg_color', ChoiceType::class, [
                    'label' => 'Couleur de la bannière',
                    'required' => false,
                    'choices' => Color::CHOICES,
                ])
                ->add('display_block', CheckboxType::class, [
                    'label' => 'Afficher la bannière',
                    'required' => false,
                ])
            ;
        }

        if ($this->getSubject()->getMedia() && $this->getSubject()->getMedia()->isVideo()) {
            $form
                ->add('video_controls', CheckboxType::class, [
                    'label' => 'Controles de la vidéo',
                    'required' => false,
                ])
                ->add('video_autoplay_loop', CheckboxType::class, [
                    'label' => 'Lancement de la vidéo automatique en boucle',
                    'required' => false,
                ])
            ;
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('position_name', null, [
                'label' => 'Bloc',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
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

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->andWhere(sprintf('%s.forRenaissance = :forRenaissance', $query->getRootAliases()[0]))
            ->setParameter('forRenaissance', $this->isRenaissanceHomeBlock())
        ;

        return $query;
    }

    protected function isRenaissanceHomeBlock(): bool
    {
        return false;
    }
}
