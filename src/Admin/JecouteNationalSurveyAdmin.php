<?php

namespace App\Admin;

use App\Form\Admin\JecouteAdminSurveyQuestionFormType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class JecouteNationalSurveyAdmin extends AbstractAdmin implements ReorderableAdminInterface
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name', null, [
                'show_filter' => true,
            ])
            ->add('createdByAdministrator.emailAddress', null, [
                'label' => "Email de l'auteur",
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('getQuestionsCount', null, [
                'label' => 'Nombre de questions',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'stats' => [
                        'template' => 'admin/jecoute/list_action_stats.html.twig',
                    ],
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

    public function getListMapperEndColumns(): array
    {
        return ['export'];
    }
}
