<?php

namespace App\Assessor\Filter;

use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Exception\ProcurationException;
use Doctrine\ORM\Query\Expr\Join;
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
                ->leftJoin(
                    AssessorRequest::class,
                    'assessor_request_holder',
                    Join::WITH,
                    'assessor_request_holder.office = :holder_office AND assessor_request_holder.votePlace = '.$alias
                )
                ->leftJoin(
                    AssessorRequest::class,
                    'assessor_request_substitute',
                    Join::WITH,
                    'assessor_request_substitute.office = :substitute_office AND assessor_request_substitute.votePlace = '.$alias
                )
                ->setParameter('holder_office', AssessorOfficeEnum::HOLDER)
                ->setParameter('substitute_office', AssessorOfficeEnum::SUBSTITUTE)
                ->andWhere('assessor_request_holder.id IS NULL OR assessor_request_substitute.id IS NULL')
            ;
        } elseif (self::ASSOCIATED === $status) {
            $qb
                ->leftJoin(
                    AssessorRequest::class,
                    'assessor_request',
                    Join::WITH,
                    'assessor_request.office = :holder_office AND assessor_request.votePlace = '.$alias
                )
                ->andWhere('assessor_request.id IS NOT NULL')
                ->setParameter('holder_office', AssessorOfficeEnum::HOLDER)
            ;
        }

        if ($this->getCity()) {
            if (is_numeric($this->getCity())) {
                $qb
                    ->andWhere("FIND_IN_SET(:postalCode, $alias.postAddress.postalCode) > 0")
                    ->setParameter('postalCode', $this->getCity())
                ;
            } else {
                $qb
                    ->andWhere("$alias.postAddress.cityName LIKE :city")
                    ->setParameter('city', '%'.strtolower($this->getCity()).'%')
                ;
            }
        }

        if ($this->getCountry()) {
            $qb
                ->andWhere("$alias.postAddress.country = :country")
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
