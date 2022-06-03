<?php

namespace App\Assessor\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class CitiesFilters extends AssessorFilters
{
    public const ASSOCIATED = 'associated';

    public static function fromRequest(Request $request)
    {
        $filters = parent::fromRequest($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::ASSOCIATED));

        return $filters;
    }

    public function setStatus(?string $status = null): void
    {
        $status = mb_strtolower(trim($status));

        if (self::ASSOCIATED !== $status) {
            throw new \InvalidArgumentException(sprintf('Unexpected cities status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        if ($this->getCity()) {
            if (is_numeric($this->getCity())) {
                $qb
                    ->andWhere("FIND_IN_SET(:postalCode, $alias.postAddress.postalCode) > 0")
                    ->setParameter('postalCode', $this->getCity())
                ;
            } else {
                $qb
                    ->andWhere("LOWER($alias.postAddress.cityName) LIKE :city")
                    ->setParameter('city', '%'.strtolower($this->getCity()).'%')
                ;
            }
        }
    }
}
