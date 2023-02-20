<?php

namespace App\Repository;

use App\Membership\MembershipSourceEnum;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Doctrine\ORM\QueryBuilder;

trait MembershipTrait
{
    public function withMembershipFilter(QueryBuilder $qb, string $alias, string $membershipValue): QueryBuilder
    {
        switch ($membershipValue) {
            case RenaissanceMembershipFilterEnum::ADHERENT_OR_SYMPATHIZER_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;
                break;
            case RenaissanceMembershipFilterEnum::ADHERENT_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NOT NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;
                break;
            case RenaissanceMembershipFilterEnum::SYMPATHIZER_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;
                break;
            case RenaissanceMembershipFilterEnum::OTHERS_ADHERENT:
                $qb
                    ->andWhere("$alias.source != :source_renaissance OR $alias.source IS NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;
                break;
        }

        return $qb;
    }
}
