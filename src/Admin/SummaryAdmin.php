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
use Sonata\AdminBundle\Route\RouteCollection;
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

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $alias = $query->getRootAlias();

        $query
            ->addSelect('adherent')
            ->addSelect('boardMember')
            ->addSelect('procurationManagedArea')
            ->join("$alias.member", 'adherent')
            ->leftJoin('adherent.boardMember', 'boardMember')
            ->leftJoin('adherent.procurationManagedArea', 'procurationManagedArea')
        ;

        return $query;
    }

    public function setInterestChoices(array $interests)
    {
        $this->interests = $interests;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
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
                        return false;
                    }

                    $value = array_map('trim', explode(',', \mb_strtolower($value['value'])));

                    $postalCodeExpression = $qb->expr()->orX();

                    foreach (array_filter($value) as $key => $code) {
                        $postalCodeExpression->add("adherent.postAddress.postalCode LIKE :postalCode_$key");
                        $qb->setParameter("postalCode_$key", "$code%");
                    }

                    $qb->andWhere($postalCodeExpression);

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere('LOWER(adherent.postAddress.cityName) LIKE :cityName');
                    $qb->setParameter('cityName', '%'.\mb_strtolower($value['value']).'%');

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
                        return false;
                    }

                    $qb->andWhere('LOWER(adherent.postAddress.country) = :country');
                    $qb->setParameter('country', \mb_strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'label' => 'N\'afficher que les référents',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere('adherent.adherentReferentData.codes IS NOT NULL');

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
