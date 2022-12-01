<?php

namespace App\Admin\ExecutiveOfficeMember;

use App\Entity\Biography\ExecutiveOfficeRoleEnum;
use App\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractExecutiveOfficeMemberAdmin extends AbstractAdmin
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
            ->with('Général', ['class' => 'col-md-6'])
                ->add('firstName', TextType::class, [
                    'label' => 'Prénom',
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('job', TextType::class, [
                    'label' => 'Poste',
                ])
                ->add('role', ChoiceType::class, [
                    'label' => 'Rôle',
                    'required' => false,
                    'choices' => ExecutiveOfficeRoleEnum::ALL,
                    'choice_label' => function (string $role) {
                        return 'executive_office_role.'.$role;
                    },
                ])
                ->add('description', TextType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'La description de la biographie sera présente dans la liste des membres. (255 caractères maximum).',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'ck-editor'],
                    'purify_html_profile' => 'enrich_content',
                    'help' => 'Le contenu de la biographie sera présent dans la fiche du membre.',
                ])
                ->add('published', null, [
                    'label' => 'Publié',
                    'required' => false,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'required' => false,
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 1 Mo et ne doit pas faire plus de 1024x1024px.',
                ])
            ->end()
            ->with('Réseaux sociaux', ['class' => 'col-md-6'])
                ->add('facebookProfile', TextType::class, [
                    'label' => 'Facebook',
                    'required' => false,
                ])
                ->add('twitterProfile', TextType::class, [
                    'label' => 'Twitter',
                    'required' => false,
                ])
                ->add('instagramProfile', TextType::class, [
                    'label' => 'Instagram',
                    'required' => false,
                ])
                ->add('linkedInProfile', TextType::class, [
                    'label' => 'LinkedIn',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('job', null, [
                'label' => 'Poste',
            ])
            ->add('role', ChoiceFilter::class, [
                'label' => 'Rôle',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ExecutiveOfficeRoleEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "executive_office_role.$choice";
                    },
                ],
            ])
            ->add('published', null, [
                'label' => 'Publié',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de dernière mise à jour',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('firstName', null, [
                'label' => 'Prénom',
            ])
            ->addIdentifier('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('job', null, [
                'label' => 'Poste',
            ])
            ->add('_image', 'thumbnail', [
                'label' => 'Photo',
                'virtual_field' => true,
            ])
            ->add('role', 'trans', [
                'label' => 'Rôle',
                'format' => 'executive_office_role.%s',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'link' => [
                        'template' => 'admin/biography/executive_office_member/link.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->andWhere(sprintf('%s.forRenaissance = :forRenaissance', $query->getRootAliases()[0]))
            ->setParameter('forRenaissance', $this->isForRenaissance())
        ;

        return $query;
    }

    protected function isForRenaissance(): bool
    {
        return false;
    }
}
