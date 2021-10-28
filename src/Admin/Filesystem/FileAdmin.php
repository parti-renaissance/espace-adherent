<?php

namespace App\Admin\Filesystem;

use App\Admin\AbstractAdmin;
use App\Entity\Administrator;
use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Filesystem\FileTypeEnum;
use App\Filesystem\FileManager;
use App\Form\Admin\Filesystem\FileParentType;
use App\Form\Admin\Filesystem\FilePermissionType;
use App\Repository\Filesystem\FileRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdmin extends AbstractAdmin
{
    /** @var FileManager */
    private $fileManager;
    /** @var FileRepository */
    private $repository;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        FileRepository $repository,
        FileManager $fileManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->repository = $repository;
        $this->fileManager = $fileManager;
    }

    public function createQuery($context = 'list')
    {
        $queryBuilder = parent::createQuery($context);

        if ('list' === $context) {
            $queryBuilder
                ->leftJoin('o.parent', 'parent')
                ->leftJoin('o.permissions', 'permission')
                ->leftJoin('o.createdBy', 'createdBy')
                ->leftJoin('o.updatedBy', 'updatedBy')
                ->addSelect('parent', 'permission', 'createdBy', 'updatedBy')
                ->orderBy('parent.name', 'ASC')
                ->addOrderBy('o.name', 'ASC')
            ;
        }

        return $queryBuilder;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('permissions', CallbackFilter::class, [
                'label' => 'Rôles',
                'mapped' => false,
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => FilePermissionEnum::toArray(),
                    'choice_label' => function (string $choice) {
                        return "file_permission.$choice";
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!isset($value['value']) || null === $value['value']) {
                        return;
                    }

                    $qb
                        ->leftJoin("$alias.permissions", 'pm')
                        ->andWhere('pm.name IN (:roles)')
                        ->setParameter(':roles', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('managedBy', CallbackFilter::class, [
                'mapped' => false,
                'show_filter' => true,
                'label' => 'Administrateur',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Administrator::class,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!isset($value['value']) || null === $value['value']) {
                        return;
                    }

                    $qb
                        ->andWhere("($alias.createdBy IN (:admins) OR $alias.updatedBy IN (:admins))")
                        ->setParameter('admins', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('extension', null, [
                'label' => 'Extension de fichier',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date d\'ajout',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Date de modification',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => FileTypeEnum::toArray(),
                    'choice_label' => function (?string $choice) {
                        return 'file.type.'.$choice;
                    },
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var File $file */
        $file = $this->getSubject();
        $isCreation = $this->isCreation();
        $isDir = $file->isDir();

        $formMapper
            ->with('Général', ['class' => 'col-md-7'])
                ->add('name', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('parent', FileParentType::class, [
                    'required' => false,
                    'label' => 'Dossier',
                    'help' => 'Saisissez le nom pour voir les dossiers existants. Si vous ne trouvez pas le dossier souhaité mettez le nom complet d\'un nouveau dossier.',
                    'disabled' => !$isCreation && $isDir,
                ])
                ->add('displayed', null, [
                    'label' => 'Affiché',
                ])
            ->end()
        ;

        if (!$isDir) {
            $formMapper
                ->with('Permissions', ['class' => 'col-md-5'])
                    ->add(
                        'permissions',
                        CollectionType::class,
                        [
                            'entry_type' => FilePermissionType::class,
                            'required' => false,
                            'label' => 'Permissions',
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false,
                        ]
                    )
                ->end()
                ->with('Source', ['class' => 'col-md-7'])
                    ->add('file', FileType::class, [
                        'label' => 'Ajouter un fichier',
                        'required' => false,
                        'help' => 'Le fichier ne doit pas dépasser 5 Mo.'.(!$isCreation ? '' : ' Laissez vide pour ne pas modifier'),
                    ])
                    ->add('externalLink', UrlType::class, [
                        'label' => 'URL',
                        'required' => false,
                    ])
                ->end()
            ;
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('fullPath', null, [
                'label' => 'Dossier/Document',
            ])
            ->add('permissions', null, [
                'label' => 'Rôles',
                'template' => 'admin/filesystem/list_permissions.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date d\'ajout',
            ])
            ->add('createdBy', null, [
                'label' => 'Créé par',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de modification',
            ])
            ->add('updatedBy', null, [
                'label' => 'Modifié par',
            ])
            ->add('displayed', null, [
                'label' => 'Est affiché ?',
                'editable' => true,
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/filesystem/list_actions.html.twig',
            ])
        ;
    }

    public function prePersist($file)
    {
        parent::prePersist($file);

        $this->fileManager->update($file);
    }

    public function postPersist($file): void
    {
        parent::postPersist($file);

        if ($file instanceof File && $file->getFile() instanceof UploadedFile) {
            $this->fileManager->upload($file);
        }
    }

    public function preUpdate($file)
    {
        parent::preUpdate($file);

        $this->fileManager->update($file);
    }

    public function postUpdate($file): void
    {
        if ($file->getFile() instanceof UploadedFile) {
            $this->fileManager->upload($file);
        } elseif ($file->isLink()) {
            $this->fileManager->removeFromStorage($file);
        }
    }

    public function preRemove($object)
    {
        parent::preRemove($object);

        $this->children = [];
        if ($object->isDir()) {
            $this->children = $this->repository->findBy(['parent' => $object]);
        }
    }

    public function postRemove($object)
    {
        parent::postRemove($object);

        foreach ($this->children as $child) {
            $this->fileManager->remove($child);
        }

        $this->fileManager->remove($object);
    }
}
