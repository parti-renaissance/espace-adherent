<?php

namespace AppBundle\Admin;

use AppBundle\Entity\HomeBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HomeBlockAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    /**
     * @param HomeBlock $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getTitle(), $object->getSubtitle(), $object->getMedia()->getPath());
    }

    public function getTemplate($name)
    {
        if ('outer_list_rows_mosaic' === $name) {
            return 'admin/media/mosaic.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        if (null === $this->getSubject()->getId()) {
            // Creation
            $formMapper
                ->add('position', null, [
                    'label' => 'Position du bloc',
                ])
                ->add('position_name', TextType::class, [
                    'label' => 'Nom du bloc',
                ])
            ;
        }

        $formMapper
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
                'filter_emojis' => true,
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
                'required' => false,
                'filter_emojis' => true,
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
            $formMapper
                ->add('display_titles', CheckboxType::class, [
                    'label' => 'Afficher le titre et sous-titre',
                    'required' => false,
                ])
            ;
        }

        if (HomeBlock::TYPE_BANNER === $this->getSubject()->getType()) {
            $formMapper
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
            $formMapper
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('position_name', null, [
                'label' => 'Bloc',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
