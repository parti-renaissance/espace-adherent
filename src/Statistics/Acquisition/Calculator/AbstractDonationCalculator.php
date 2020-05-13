<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Repository\DonationRepository;
use App\Statistics\Acquisition\Calculator\Category\DonationCategoryTrait;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractDonationCalculator extends AbstractCalculator
{
    use DonationCategoryTrait;

    protected $repository;

    public function __construct(DonationRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function addTagFilter(QueryBuilder $qb, array $tags): void
    {
        $orParts = ['donation.postAddress.country IN (:tags)'];
        $params = ['tags' => $tags];

        array_walk($tags, function ($tag, $key) use (&$params, &$orParts) {
            $key = 'tag'.$key;
            $orParts[] = 'donation.postAddress.postalCode LIKE :'.$key;
            $params[$key] = $tag.'%';
        });

        $qb->andWhere((new Orx())->addMultiple($orParts));

        array_walk($params, function ($value, $key) use ($qb) {
            $qb->setParameter($key, $value);
        });
    }

    abstract protected function getDonationStatus(): string;

    abstract protected function getDonationDuration(): int;
}
