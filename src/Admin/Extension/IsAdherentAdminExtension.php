<?php

namespace AppBundle\Admin\Extension;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class IsAdherentAdminExtension extends AbstractAdminExtension
{
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
        /** @var QueryBuilder $query */
        $query
            ->addSelect('CASE WHEN adherent.id IS NOT NULL THEN 1 ELSE 0 END AS is_adherent')
            ->leftJoin(
                Adherent::class,
                'adherent',
                Join::WITH,
                sprintf('adherent.emailAddress = %s.emailAddress', $query->getRootAliases()[0])
            )
        ;
    }
}
