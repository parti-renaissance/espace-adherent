<?php

namespace App\Assessor\Filter;

use App\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class VotePlaceFilters extends AssessorFilters
{
    public const UNASSOCIATED = 'unassociated';
    public const ASSOCIATED = 'associated';

    public static function fromRequest(Request $request)
    {
        $filters = parent::fromRequest($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNASSOCIATED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if (!\in_array($status, [self::ASSOCIATED, self::UNASSOCIATED])) {
            throw new ProcurationException(sprintf('Unexpected vote place status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $status = $this->getStatus();

        if (self::UNASSOCIATED === $status) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $alias.'.substituteOfficeAvailable = true',
                        $alias.'.holderOfficeAvailable = true'
                    )
                )
            ;
        } elseif (self::ASSOCIATED === $status) {
            $qb
                ->andWhere($alias.'.substituteOfficeAvailable = false')
                ->andWhere($alias.'.holderOfficeAvailable = false')
            ;
        }

        if ($this->getCity()) {
            if (is_numeric($this->getCity())) {
                $qb
                    ->andWhere("FIND_IN_SET(:postalCode, $alias.postalCode) > 0")
                    ->setParameter('postalCode', $this->getCity())
                ;
            } else {
                $qb
                    ->andWhere("LOWER($alias.city) LIKE :city")
                    ->setParameter('city', '%'.strtolower($this->getCity()).'%')
                ;
            }
        }

        if ($this->getCountry()) {
            $qb
                ->andWhere("$alias.country = :country")
                ->setParameter('country', $this->getCountry())
            ;
        }

        if ($this->getVotePlace()) {
            if (preg_match(AssessorFilters::VOTE_PLACE_CODE_REGEX, $this->getVotePlace())) {
                $qb
                    ->andWhere("$alias.code = :code")
                    ->setParameter('code', $this->getVotePlace())
                ;
            } else {
                $qb
                    ->andWhere("$alias.name LIKE :name")
                    ->setParameter('name', '%'.strtolower($this->getVotePlace()).'%')
                ;
            }
        }

        $qb->addOrderBy("$alias.name", 'DESC');
    }
}
