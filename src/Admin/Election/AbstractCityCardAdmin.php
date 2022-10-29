<?php

namespace App\Admin\Election;

use App\Entity\Election\CityCard;
use App\Entity\Election\CityContact;
use App\Entity\Election\CityVoteResult;
use App\Entity\Election\MinistryVoteResult;
use App\Entity\VotePlace;
use App\Form\EventListener\CityCardListener;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AbstractCityCardAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept([
            'list',
            'edit',
            'export',
        ]);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var QueryBuilder $query */
        $query
            ->innerJoin(current($query->getRootAliases()).'.city', 'city')
            ->innerJoin('city.department', 'department')
            ->innerJoin('department.region', 'region')
            ->addSelect('city', 'department', 'region')
        ;

        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->getFormBuilder()
            ->addEventSubscriber(new CityCardListener())
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
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
            ->add('priority', CallbackFilter::class, [
                'label' => 'Priorité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge(CityCard::PRIORITY_CHOICES, ['without']),
                    'choice_label' => function (string $choice) {
                        return "election.city_card.priority.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if ('without' === $value->getValue()) {
                        $qb->andWhere($alias.'.priority IS NULL');
                    } else {
                        $qb
                            ->andWhere($alias.'.priority = :priority')
                            ->setParameter('priority', $value->getValue())
                        ;
                    }

                    return true;
                },
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
                    ],
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->leftJoin(
                            MinistryVoteResult::class,
                            'ministry_vote_result',
                            Expr\Join::WITH,
                            "ministry_vote_result.city = $alias.city AND ministry_vote_result.updatedBy IS NOT NULL"
                        )
                    ;

                    if (\in_array($value->getValue(), ['Ville', 'Bureaux'])) {
                        $qb->leftJoin(
                            CityVoteResult::class,
                            'city_vote_result',
                            Expr\Join::WITH,
                            "city_vote_result.city = $alias.city"
                        );
                    }

                    if ('Bureaux' === $value->getValue()) {
                        $qb
                            ->leftJoin(
                                VotePlace::class,
                                'vote_place',
                                Expr\Join::WITH,
                                'SUBSTRING(vote_place.code, 1, 5) = city.inseeCode'
                            )
                            ->leftJoin('vote_place.voteResults', 'vote_place_result')
                        ;
                    }

                    switch ($value->getValue()) {
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
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
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

    protected function configureListFields(ListMapper $list): void
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureExportFields(): array
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
