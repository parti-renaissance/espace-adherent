<?php

namespace App\Admin;

use App\Entity\LegislativeCandidate;
use App\Form\GenderType;
use App\ValueObject\Genders;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class LegislativeCandidateAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];
    protected $maxPerPage = 100;
    protected $perPageOptions = [];

    protected $formOptions = [
        'validation_groups' => ['Default', 'Admin'],
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
            ->add('districtZone', null, [
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
                'template' => 'admin/legislative_candidate/list_thumbnail.html.twig',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('districtNumber', null, [
                'label' => 'Circonscription',
            ])
            ->add('districtZone', null, [
                'label' => 'Zone géographique',
            ])
            ->add('status', null, [
                'label' => 'Résultat',
                'template' => 'admin/legislative_candidate/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/legislative_candidate/list_actions.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('Informations personnelles', ['class' => 'col-md-4'])
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
            ->with('Circonscription', ['class' => 'col-md-4'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Résultat',
                    'choices' => [
                        'Pas de résultat' => LegislativeCandidate::STATUS_NONE,
                        'Non qualifié(e)' => LegislativeCandidate::STATUS_LOST,
                        'Qualifié(e)' => LegislativeCandidate::STATUS_QUALIFIED,
                        'Elu(e)' => LegislativeCandidate::STATUS_WON,
                    ],
                ])
                ->add('districtZone', null, [
                    'label' => 'Zone géographique',
                ])
                ->add('districtNumber', null, [
                    'label' => 'Numéro de la circonscription',
                ])
                ->add('districtName', null, [
                    'label' => 'Nom de la circonscription',
                    'attr' => [
                        'placeholder' => 'Deuxième circonscription des Hauts-de-Seine',
                    ],
                ])
                ->add('geojson', TextareaType::class, [
                    'label' => 'Données GeoJSON',
                    'required' => false,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-4'])
                ->add('media', null, [
                    'label' => false,
                ])
            ->end()
            ->with('Parcours personnel', ['class' => 'col-md-9'])
                ->add('career', ChoiceType::class, [
                    'label' => 'Carrière',
                    'choices' => [
                        'Vie civile' => LegislativeCandidate::CAREERS[0],
                        'Vie politique' => LegislativeCandidate::CAREERS[1],
                    ],
                ])
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
                ->add('donationPageUrl', UrlType::class, [
                    'label' => 'Page de dons',
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

    protected function configureShowFields(ShowMapper $mapper)
    {
        $mapper
            ->with('Informations générales', ['class' => 'col-md-5'])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
            ->end()
            ->with('Circonscription', ['class' => 'col-md-7'])
                ->add('districtZone', null, [
                    'label' => 'Zone géographique',
                ])
                ->add('districtName', null, [
                    'label' => 'Nom de la circonscription',
                ])
                ->add('districtNumber', null, [
                    'label' => 'Numéro de la circonscription',
                ])
                ->add('geojson', null, [
                    'label' => 'Données GeoJSON',
                ])
            ->end()
            ->with('Photo de profil', ['class' => 'col-md-5'])
                ->add('media', null)
            ->end()
            ->with('Pages Web', ['class' => 'col-md-7'])
                ->add('websiteUrl', 'url', [
                    'label' => 'Site Web',
                ])
                ->add('donationPageUrl', 'url', [
                    'label' => 'Donations',
                ])
                ->add('twitterPageUrl', 'url', [
                    'label' => 'Twitter',
                ])
                ->add('facebookPageUrl', 'url', [
                    'label' => 'Facebook',
                ])
            ->end()
            ->with('Description', ['class' => 'col-md-12'])
                ->add('description', null)
            ->end()
        ;
    }
}
