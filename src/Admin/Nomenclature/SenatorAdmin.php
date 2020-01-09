<?php

namespace AppBundle\Admin\Nomenclature;

use AppBundle\Admin\MediaSynchronisedAdminTrait;
use AppBundle\Entity\Nomenclature\Senator;
use AppBundle\Form\GenderType;
use AppBundle\ValueObject\Genders;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class SenatorAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('gender', null, [
                'label' => 'Genre',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => Genders::CHOICES,
                ],
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
                'show_filter' => true,
            ])
            ->add('area', null, [
                'label' => 'Zone géographique',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('_thumbnail', null, [
                'label' => 'Photo',
                'virtual_field' => true,
                'template' => 'admin/nomenclature/senator/list_thumbnail.html.twig',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('area', null, [
                'label' => 'Zone',
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'template' => 'admin/nomenclature/senator/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('Informations personnelles', ['class' => 'col-md-4'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Visibilité',
                    'choices' => [
                        'Visible' => Senator::ENABLED,
                        'Masqué' => Senator::DISABLED,
                    ],
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'expanded' => false,
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Dumoulin',
                    ],
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Alexandre',
                    ],
                ])
                ->add('emailAddress', EmailType::class, [
                    'label' => 'Adresse e-mail',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'alexandre@dumoulin.com',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Identifiant URL',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'alexandre-dumoulin',
                    ],
                    'help' => 'Laissez le champ vide pour que le système génère cette valeur automatiquement',
                ])
            ->end()
            ->with('Zone', ['class' => 'col-md-4'])
                ->add('area', null, [
                    'label' => 'Zone géographique',
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-4'])
                ->add('media', null, [
                    'label' => false,
                ])
            ->end()
            ->with('Parcours personnel', ['class' => 'col-md-9'])
                ->add('description', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
            ->with('Liens Web', ['class' => 'col-md-3'])
                ->add('websiteUrl', UrlType::class, [
                    'label' => 'Page personnelle',
                    'required' => false,
                ])
                ->add('twitterPageUrl', UrlType::class, [
                    'label' => 'Page Twitter',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://twitter.com/alexandredumoulin',
                    ],
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Page Facebook',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://facebook.com/alexandre-dumoulin',
                    ],
                ])
            ->end()
        ;
    }
}
