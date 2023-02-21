<?php

namespace App\Repository\Helper;

use App\Membership\MembershipSourceEnum;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Doctrine\ORM\QueryBuilder;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

final class MembershipFilterHelper
{
    public static function withMembershipFilter(
        ProxyQuery|QueryBuilder $qb,
        string $alias,
        string $membershipValue
    ): bool {
        switch ($membershipValue) {
            case RenaissanceMembershipFilterEnum::ADHERENT_OR_SYMPATHIZER_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;

                return true;
            case RenaissanceMembershipFilterEnum::ADHERENT_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NOT NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;

                return true;
            case RenaissanceMembershipFilterEnum::SYMPATHIZER_RE:
                $qb
                    ->andWhere("$alias.source = :source_renaissance AND $alias.lastMembershipDonation IS NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;

                return true;
            case RenaissanceMembershipFilterEnum::OTHERS_ADHERENT:
                $qb
                    ->andWhere("$alias.source != :source_renaissance OR $alias.source IS NULL")
                    ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
                ;

                return true;
        }

        return false;
    }
}
