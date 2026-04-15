<?php

declare(strict_types=1);

namespace App\Admin\Email;

use App\Admin\AbstractAdmin;
use App\Entity\Geo\Zone;
use App\Entity\Scope;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\UnlayerContentType;
use App\Form\DataTransformer\ScopeToCodeDataTransformer;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmailTemplateAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly int $emailTemplateUnlayerTemplateId,
        private readonly ScopeToCodeDataTransformer $dataTransformer,
    ) {
        parent::__construct();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('label', null, [
                'label' => 'Label',
                'show_filter' => true,
            ])
            ->add('isStatutory', null, [
                'label' => 'Statutaire',
                'show_filter' => true,
            ])
            ->add('scopes', CallbackFilter::class, [
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Scope::class,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere(\sprintf('FIND_IN_SET(:scope, %s.scopes) > 0', $alias))
                        ->setParameter('scope', $value->getValue()->getCode())
                    ;

                    return true;
                },
                'label' => 'Scope',
            ])
            ->add('zones', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Zones',
                'field_type' => AdminZoneAutocompleteType::class,
                'field_options' => [
                    'class' => Zone::class,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'zone_types' => [Zone::DEPARTMENT],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('label', null, ['label' => 'Label'])
            ->add('scopes', 'array_list', ['label' => 'Scopes'])
            ->add('zones', 'array_list', ['label' => 'Zones'])
            ->add('isStatutory', null, ['label' => 'Statutaire'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                ])
                ->add('subject', null, ['label' => 'Objet', 'required' => false])
                ->add('subjectEditable', null, ['label' => 'Objet modifiable', 'required' => false])
                ->add('isStatutory', null, [
                    'label' => 'Statutaire',
                    'required' => false,
                ])
            ->end()
            ->with('Rôle & Périmètre', ['class' => 'col-md-6'])
                ->add('scopes', EntityType::class, [
                    'label' => 'Scopes',
                    'class' => Scope::class,
                    'multiple' => true,
                ])
                ->add('zones', AdminZoneAutocompleteType::class, [
                    'label' => 'Zones locales',
                    'multiple' => true,
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                    'zone_types' => [Zone::DEPARTMENT],
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
        $form->get('scopes')->addModelTransformer($this->dataTransformer);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query->andWhere(\sprintf('%s.createdByAdministrator IS NOT NULL', $query->getRootAliases()[0]));

        return $query;
    }
}
