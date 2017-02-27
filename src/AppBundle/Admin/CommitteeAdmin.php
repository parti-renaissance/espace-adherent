<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Committee;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommitteeAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Comité', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('facebookPageUrl', 'url', [
                    'label' => 'Facebook',
                ])
                ->add('twitterNickname', null, [
                    'label' => 'Twitter',
                ])
                ->add('googlePlusPageUrl', 'url', [
                    'label' => 'Google+',
                ])
                ->add('status', null, [
                    'label' => 'Status',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('approvedAt', null, [
                    'label' => 'Date d\'approbation',
                ])
                ->add('refusedAt', null, [
                    'label' => 'Date de refus',
                ])
            ->end()
            ->with('Adresse', array('class' => 'col-md-5'))
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Comité', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => [
                        'Approuvé' => Committee::APPROVED,
                        'En attente d\'approbation' => Committee::PENDING,
                        'Refusé' => Committee::REFUSED,
                    ],
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('facebookPageUrl', 'url', [
                    'label' => 'Facebook',
                    'required' => false,
                ])
                ->add('twitterNickname', null, [
                    'label' => 'Twitter',
                    'required' => false,
                ])
                ->add('googlePlusPageUrl', 'url', [
                    'label' => 'Google+',
                    'required' => false,
                ])
            ->end()
            ->with('Adresse', array('class' => 'col-md-5'))
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('postalCode', 'doctrine_orm_callback', [
                'label' => 'Code postal',
                'field_type' => 'text',
                'callback' => function ($qb, $alias, $field, $value) {
                    /* @var QueryBuilder $qb */

                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.postAddress.postalCode', $alias).' LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => Committee::PENDING,
                    'Approuvé' => Committee::APPROVED,
                    'Refusé' => Committee::REFUSED,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('cityName', null, [
                'label' => 'Ville',
            ])
            ->add('isWaitingForApproval', 'boolean', [
                'label' => 'En attente ?',
            ])
            ->add('isApproved', 'boolean', [
                'label' => 'Approuvé ?',
            ])
            ->add('isRefused', 'boolean', [
                'label' => 'Refusé ?',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'approve' => [
                        'template' => 'admin/committee_approve.html.twig',
                    ],
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }
}
