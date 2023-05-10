<?php

namespace App\Admin;

use App\Entity\Geo\Zone;
use App\Form\Admin\UnlayerContentType;
use App\Scope\ScopeEnum;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmailTemplateAdmin extends AbstractAdmin
{
    private int $emailTemplateUnlayerTemplateId;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        string $emailTemplateUnlayerTemplateId
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->emailTemplateUnlayerTemplateId = (int) $emailTemplateUnlayerTemplateId;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
            ->add('scopes', CallbackFilter::class, [
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ScopeEnum::ALL,
                    'choice_label' => function (string $label) {
                        return 'scope.'.$label;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere(sprintf('%1$s.scopes IS NOT NULL AND FIND_IN_SET(:scope, %1$s.scopes) > 0', $alias))
                        ->setParameter('scope', $value->getValue())
                    ;

                    return true;
                },
                'label' => 'Scope',
            ])
            ->add('zones', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Zones',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'code',
                    ],
                    'callback' => function (AdminInterface $admin, array $property, $value): void {
                        $datagrid = $admin->getDatagrid();
                        $query = $datagrid->getQuery();
                        $rootAlias = $query->getRootAlias();
                        $query
                            ->andWhere($rootAlias.'.type IN (:types)')
                            ->setParameter('types', [Zone::DEPARTMENT])
                        ;

                        $datagrid->setValue($property[0], null, $value);
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('label', null, [
                'label' => 'Label',
            ])
            ->add('scopes', null, [
                'label' => 'Scopes',
                'template' => 'admin/email_template/list_scopes.html.twig',
            ])
            ->add('zones', null, [
                'label' => 'Zones',
                'template' => 'admin/email_template/list_zones.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                ])
                ->add('scopes', ChoiceType::class, [
                    'label' => 'Scopes',
                    'choices' => ScopeEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return 'scope.'.$choice;
                    },
                    'multiple' => true,
                ])
                ->add('zones', ModelAutocompleteType::class, [
                    'property' => ['name', 'code'],
                    'label' => 'Zones locales',
                    'multiple' => true,
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                    'callback' => [$this, 'prepareZoneAutocompleteCallback'],
                ])
            ->end()
            ->with('Contenu')
                ->add('jsonContent', HiddenType::class)
                ->add('content', UnlayerContentType::class, [
                    'label' => false,
                    'unlayer_template_id' => $this->emailTemplateUnlayerTemplateId,
                ])
            ->end()
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->andWhere(sprintf('%s.createdByAdministrator IS NOT NULL', $query->getRootAliases()[0]))
        ;

        return $query;
    }

    public static function prepareZoneAutocompleteCallback(
        AdminInterface $admin,
        array $properties,
        string $value
    ): void {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }
        $qb
            ->orWhere($orx)
            ->andWhere(sprintf('%1$s.type = :type AND %1$s.active = 1', $alias))
            ->setParameter('type', Zone::DEPARTMENT)
        ;
    }
}
