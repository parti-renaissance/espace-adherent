<?php

namespace AppBundle\Admin\Election;

use AppBundle\Entity\Election\CityCard;
use AppBundle\Entity\Election\CityContact;
use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use AppBundle\Entity\VotePlace;
use AppBundle\Form\EventListener\CityCardListener;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AbstractCityCardAdmin extends AbstractAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept([
            'list',
            'edit',
            'export',
        ]);
    }

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);

        $proxyQuery
            ->innerJoin('o.city', 'city')
            ->innerJoin('city.department', 'department')
            ->innerJoin('department.region', 'region')
            ->addSelect('city, department, region')
        ;

        return $proxyQuery;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->getFormBuilder()
            ->addEventSubscriber(new CityCardListener())
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('city.name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('city.inseeCode', null, [
                'label' => 'Code INSEE',
                'show_filter' => true,
            ])
            ->add('city.postalCodes', null, [
                'label' => 'Code postal',
                'show_filter' => true,
            ])
            ->add('city.department', null, [
                'label' => 'Département',
                'multiple' => true,
                'show_filter' => true,
            ])
            ->add('city.department.region', null, [
                'label' => 'Région',
                'multiple' => true,
                'show_filter' => true,
            ])
            ->add('priority', ChoiceFilter::class, [
                'label' => 'Priorité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => CityCard::PRIORITY_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "election.city_card.priority.$choice";
                    },
                ],
            ])
            ->add('resultsType', CallbackFilter::class, [
                'label' => 'Niveau de remontée',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Ministère',
                        'Ville',
                        'Bureaux',
                        'Aucun',
                    ],
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->leftJoin(
                            MinistryVoteResult::class,
                            'ministry_vote_result',
                            Expr\Join::WITH,
                            "ministry_vote_result.city = $alias.city AND ministry_vote_result.updatedBy IS NOT NULL"
                        )
                        ->leftJoin(
                            CityVoteResult::class,
                            'city_vote_result',
                            Expr\Join::WITH,
                            "city_vote_result.city = $alias.city"
                        )
                        ->leftJoin(
                            VotePlace::class,
                            'vote_place',
                            Expr\Join::WITH,
                            'SUBSTRING(vote_place.code, 1, 5) = city.inseeCode'
                        )
                        ->leftJoin('vote_place.voteResults', 'vote_place_result')
                    ;

                    switch ($value['value']) {
                        case 'Ministère':
                            $qb->andWhere('ministry_vote_result IS NOT NULL');

                            break;
                        case 'Ville':
                            $qb
                                ->andWhere('city_vote_result IS NOT NULL')
                                ->andWhere('ministry_vote_result IS NULL')
                            ;

                            break;
                        case 'Bureaux':
                            $qb
                                ->andWhere('vote_place_result IS NOT NULL')
                                ->andWhere('city_vote_result IS NULL')
                                ->andWhere('ministry_vote_result IS NULL')
                            ;

                            break;
                        case 'Aucun':
                            $qb
                                ->andWhere('vote_place_result IS NULL')
                                ->andWhere('city_vote_result IS NULL')
                                ->andWhere('ministry_vote_result IS NULL')
                            ;

                            break;
                        default:
                            return false;
                    }

                    return true;
                },
            ])
            ->add('allContactsDone', CallbackFilter::class, [
                'label' => 'Personnes contactées',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Oui',
                        'Non',
                    ],
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
                        case 'Oui':
                            $qb
                                ->innerJoin("$alias.contacts", 'contact')
                                ->leftJoin(
                                    CityContact::class,
                                    'contact_not_done',
                                    Expr\Join::WITH,
                                    "contact_not_done.city = $alias AND contact_not_done.done = :contact_not_done"
                                )
                                ->setParameter('contact_not_done', false)
                                ->andWhere('contact_not_done IS NULL')
                            ;

                            break;
                        case 'Non':
                            $qb
                                ->innerJoin("$alias.contacts", 'contact')
                                ->leftJoin(
                                    CityContact::class,
                                    'contact_not_done',
                                    Expr\Join::WITH,
                                    "contact_not_done.city = $alias AND contact_not_done.done = :contact_not_done"
                                )
                                ->setParameter('contact_not_done', false)
                                ->andWhere('contact_not_done IS NOT NULL')
                            ;

                            break;
                        default:
                            return false;
                    }

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('city.name', null, [
                'label' => 'Nom',
            ])
            ->add('city.inseeCode', null, [
                'label' => 'Code INSEE',
            ])
            ->add('city.department', null, [
                'label' => 'Département',
            ])
            ->add('city.department.region', null, [
                'label' => 'Région',
            ])
            ->add('priority', null, [
                'label' => 'Priorité',
                'template' => 'admin/election/city_card/_list_priority.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Nom' => 'city.name',
            'Code INSEE' => 'city.inseeCode',
            'Département' => 'city.department',
            'Région' => 'city.department.region',
        ];
    }
}
