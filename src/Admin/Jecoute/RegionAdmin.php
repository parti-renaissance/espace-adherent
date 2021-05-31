<?php

namespace App\Admin\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use App\Jecoute\RegionManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class RegionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    private $regionManager;

    public function __construct(string $code, string $class, string $baseControllerName, RegionManager $regionManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->regionManager = $regionManager;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Region $region */
        $region = $this->getSubject();

        $formMapper
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('zone', EntityType::class, [
                    'label' => 'Zone',
                    'class' => Zone::class,
                ])
                ->add('subtitle', TextType::class, [
                    'label' => 'Sous-titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                ])
                ->add('primaryColor', ChoiceType::class, [
                    'choices' => RegionColorEnum::all(),
                    'choice_label' => function (string $choice) {
                        return "common.$choice";
                    },
                    'label' => 'Couleur',
                ])
                ->add('externalLink', UrlType::class, [
                    'label' => 'Lien',
                    'required' => false,
                ])
            ->end()
            ->with('Fichiers', ['class' => 'col-md-6'])
                ->add('logoFile', FileType::class, [
                    'label' => 'Logo',
                    'required' => !$region->hasLogoUploaded(),
                    'attr' => ['accept' => 'image/*'],
                    'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                ])
                ->add('bannerFile', FileType::class, [
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                    'label' => 'Bannière',
                    'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                ])
                ->add('removeBanner', CheckboxType::class, [
                    'label' => 'Supprimer la bannière ?',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('zone.name', null, [
                'label' => 'Nom',
            ])
            ->add('zone.code', 'color', [
                'label' => 'Code',
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

    /**
     * @param Region $region
     */
    public function prePersist($region)
    {
        parent::prePersist($region);

        $this->regionManager->handleFile($region);
    }

    /**
     * @param Region $region
     */
    public function preUpdate($region)
    {
        parent::preUpdate($region);

        $this->regionManager->handleFile($region);
    }

    /**
     * @param Region $region
     */
    public function postRemove($region)
    {
        parent::postRemove($region);

        $this->regionManager->removeBanner($region);
    }
}
