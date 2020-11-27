<?php

namespace App\Admin\Jecoute;

use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    private $storage;

    public function __construct(string $code, string $class, string $baseControllerName, FilesystemInterface $storage)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->storage = $storage;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('code', TextType::class, [
                    'label' => 'Code',
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
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('code', 'color', [
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

        $this->handleFile($region);
    }

    /**
     * @param Region $region
     */
    public function preUpdate($region)
    {
        parent::preUpdate($region);

        $this->handleFile($region);
    }

    /**
     * @param Region $region
     */
    public function postRemove($region)
    {
        parent::postRemove($region);

        if ($region->hasBannerUploaded()) {
            $filePath = $region->getBannerPathWithDirectory();

            if ($this->storage->has($filePath)) {
                $this->storage->delete($filePath);
            }
        }
    }

    public function handleFile(Region $region): void
    {
        $filepath = $region->getBannerPathWithDirectory();

        if ($region->getRemoveBanner() && $this->storage->has($filepath)) {
            $this->storage->delete($filepath);
            $region->removeBanner();

            return;
        }

        $this->uploadLogo($region);
        $this->uploadBanner($region);
    }

    public function uploadLogo(Region $region): void
    {
        $uploadedFile = $region->getLogoFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setLogoFromUploadedFile();

        $this->storage->put(
            $region->getLogoPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
        );
    }

    public function uploadBanner(Region $region): void
    {
        $uploadedFile = $region->getBannerFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setBannerFromUploadedFile();

        $this->storage->put(
            $region->getBannerPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
        );
    }
}
