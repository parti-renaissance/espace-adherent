<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Entity\Administrator;
use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use App\Jecoute\RegionManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

abstract class AbstractRegionAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly RegionManager $regionManager,
        private readonly Security $security,
    ) {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'label';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('delete');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Region $region */
        $region = $this->getSubject();

        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('zone', ModelAutocompleteType::class, [
                    'multiple' => false,
                    'label' => 'Zone',
                    'required' => true,
                    'property' => ['name', 'code'],
                    'callback' => function (AdminInterface $admin, array $property, $value): void {
                        $datagrid = $admin->getDatagrid();
                        $query = $datagrid->getQuery();
                        $rootAlias = $query->getRootAlias();
                        $query
                            ->andWhere($rootAlias.'.type IN (:types)')
                            ->setParameter('types', $this->getZoneTypes())
                        ;

                        $datagrid->setValue($property[0], null, $value);
                    },
                    'btn_add' => false,
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
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Campagne active',
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
                ->add('removeBannerFile', CheckboxType::class, [
                    'label' => 'Supprimer la bannière ?',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('subtitle', null, [
                'label' => 'Sous-titre',
            ])
            ->add('zone.name', null, [
                'label' => 'Zone',
            ])
            ->add('zone.code', 'color', [
                'label' => 'Code',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    /**
     * @param Region $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $this->regionManager->handleFile($object);

        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setAdministrator($administrator);
    }

    /**
     * @param Region $object
     */
    protected function preUpdate(object $object): void
    {
        parent::preUpdate($object);

        $this->regionManager->handleFile($object);
    }

    /**
     * @param Region $object
     */
    protected function postRemove(object $object): void
    {
        parent::postRemove($object);

        $this->regionManager->removeBanner($object);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $rootAlias = current($query->getRootAliases());

        $query
            ->innerJoin($rootAlias.'.zone', 'zone')
            ->andWhere('zone.type IN (:types)')
            ->setParameter('types', $this->getZoneTypes())
        ;

        return $query;
    }

    abstract protected function getZoneTypes(): array;
}
