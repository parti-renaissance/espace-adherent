<?php

namespace AppBundle\Admin;

use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Summary\Contribution;
use AppBundle\Summary\JobDuration;
use AppBundle\Summary\JobLocation;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SummaryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
    ];

    private $interests = [];

    public function setInterestChoices(array $interests)
    {
        $this->interests = $interests;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('member.id', null, [
                'label' => 'ID',
            ])
            ->add('member.lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('member.firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('member.emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('member.registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('m.postAddress.postalCode LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('LOWER(m.postAddress.cityName) LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('LOWER(m.postAddress.country) = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'label' => 'N\'afficher que les référents',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('m.managedArea.codes IS NOT NULL');

                    return true;
                },
            ])
            ->add('currentProfession', null, [
                'label' => 'Métier principal',
            ])
            ->add(
                'missionTypeWishes',
                null,
                [
                    'label' => 'Type de missions',
                ],
                null,
                [
                    'multiple' => true,
                ]
            )
            ->add(
                'availabilities',
                ChoiceFilter::class,
                [
                    'label' => 'Disponibilités',
                ],
                'choice',
                [
                    'choices' => JobDuration::CHOICES,
                    'multiple' => true,
                ]
            )
            ->add(
                'skills',
                null,
                [
                    'label' => 'Compétences',
                ],
                null,
                [
                    'multiple' => true,
                ]
            )
            ->add(
                'contributionWish',
                ChoiceFilter::class,
                [
                    'label' => 'Souhait de contribution',
                ],
                'choice',
                [
                    'choices' => Contribution::CHOICES,
                    'multiple' => true,
                ]
            )
            ->add('public', null, [
                'label' => 'Visible au public',
            ])
            ->add(
                'member.position',
                ChoiceFilter::class,
                [
                    'label' => 'Situation',
                ],
                'choice',
                [
                    'choices' => ActivityPositions::CHOICES,
                    'multiple' => true,
                ]
            )
            ->add(
                'jobLocations',
                ChoiceFilter::class,
                [
                    'label' => 'Lieu de travail',
                ],
                'choice',
                [
                    'choices' => JobLocation::CHOICES,
                    'multiple' => true,
                ]
            )
            ->add('motivation', null, [
                'label' => 'Motivation',
            ])
            ->add(
                'languages',
                null,
                [
                    'label' => 'Langues',
                ],
                null,
                [
                    'multiple' => true,
                ]
            )
            ->add('experiences.company', null, [
                    'label' => 'Entreprise',
            ])
            ->add('experiences.position', null, [
                'label' => 'Poste',
            ])
            ->add(
                'member.interests',
                ChoiceFilter::class,
                [
                    'label' => 'Centres d\'intérêt',
                ],
                'choice',
                [
                    'choices' => array_flip($this->interests),
                    'multiple' => true,
                ]
            )
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('member', null, [
                'label' => 'Membre',
            ])
            ->add('currentProfession', null, [
                'label' => 'Métier principal',
            ])
            ->add('contributionWishLabel', null, [
                'label' => 'Souhait de contribution',
            ])
            ->add('availabilities', null, [
                'label' => 'Disponibilités',
                'template' => 'admin/summary/list_availabilities.html.twig',
            ])
            ->add('contactEmail', null, [
                'label' => 'Email',
            ])
            ->add('public', null, [
                'label' => 'Visible au public',
                'template' => 'admin/summary/public_show.html.twig',
            ])
        ;
    }
}
