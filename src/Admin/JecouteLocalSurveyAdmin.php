<?php

namespace App\Admin;

use App\Entity\Geo\Zone;
use App\Form\Admin\JecouteAdminSurveyQuestionFormType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Translation\TranslatorInterface;

class JecouteLocalSurveyAdmin extends AbstractAdmin
{
    /** @var TranslatorInterface */
    protected $translator;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $query
            ->addSelect('zone', 'question')
            ->leftJoin('o.zone', 'zone')
            ->leftJoin('o.questions', 'question')
        ;

        return $query;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'filter_emojis' => true,
                    'label' => 'Nom du questionnaire',
                ])
                ->add('questions', CollectionType::class, [
                    'entry_type' => JecouteAdminSurveyQuestionFormType::class,
                    'required' => false,
                    'label' => 'Questions',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publié',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('author.lastName', null, [
                'label' => "Nom de l'auteur",
                'show_filter' => true,
            ])
            ->add('author.firstName', null, [
                'label' => "Prénom de l'auteur",
                'show_filter' => true,
            ])
            ->add('zone', CallbackFilter::class, [
                'label' => 'Zones',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => Zone::class,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'code',
                    ],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    /* @var Collection|Zone[] $zones */
                    $zones = $value['value'];

                    if (\count($zones)) {
                        $ids = $zones->map(static function (Zone $zone) {
                            return $zone->getId();
                        })->toArray();

                        /* @var QueryBuilder $qb */
                        $qb
                            ->innerJoin('zone.parents', 'zone_parent')
                            ->andWhere(
                                $qb->expr()->orX(
                                    $qb->expr()->in('zone.id', $ids),
                                    $qb->expr()->in('zone_parent.id', $ids),
                                )
                            )
                        ;
                    }

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
            ])
            ->add('getQuestionsCount', null, [
                'label' => 'Nombre de questions',
            ])
            ->add('zone', null, [
                'label' => 'Zone',
                'template' => 'list_zone.html.twig',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;

        if ($this->hasAccess('show')) {
            $listMapper
                ->add('export', null, [
                    'virtual_field' => true,
                    'template' => 'admin/jecoute/_exports.html.twig',
                ])
            ;
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
