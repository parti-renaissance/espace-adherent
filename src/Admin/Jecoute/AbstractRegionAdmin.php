<?php

namespace App\Admin\Jecoute;

use App\Entity\Administrator;
use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use App\Jecoute\RegionManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Security\Core\Security;

abstract class AbstractRegionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    private $regionManager;
    private $security;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        RegionManager $regionManager,
        Security $security
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->regionManager = $regionManager;
        $this->security = $security;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Region $region */
        $region = $this->getSubject();

        $formMapper
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
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

        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $region->setAdministrator($administrator);
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

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $rootAlias = current($query->getRootAliases());

        $query
            ->innerJoin($rootAlias.('.zone'), 'zone')
            ->andWhere('zone.type IN (:types)')
            ->setParameter('types', $this->getZoneTypes())
        ;

        return $query;
    }

    abstract protected function getZoneTypes(): array;
}
