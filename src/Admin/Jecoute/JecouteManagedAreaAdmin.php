<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class JecouteManagedAreaAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.jecoute.jecoute_managed_area_admin';

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zone', ModelAutocompleteType::class, [
            'callback' => [$this, 'prepareAutocompleteFilterCallback'],
            'property' => 'name',
            'btn_add' => false,
        ]);
    }

    public static function prepareAutocompleteFilterCallback(
        AbstractAdmin $admin,
        string $property,
        string $value,
    ): void {
        $admin->getDatagrid()->setValue($property, null, $value);
        /** @var QueryBuilder $qb */
        $qb = $admin
            ->getDatagrid()
            ->getQuery()
        ;

        $alias = $qb->getRootAliases()[0];
        $qb
            ->andWhere($qb->expr()->orX(
                $qb->expr()->in($alias.'.type', ':types'),
                $alias.'.type = :borough AND '.$alias.'.name LIKE :paris',
                $alias.'.type = :region AND '.$alias.'.name = :corse'
            ))
            ->setParameter('types', [Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT])
            ->setParameter('borough', Zone::BOROUGH)
            ->setParameter('region', Zone::REGION)
            ->setParameter('paris', 'Paris %')
            ->setParameter('corse', 'Corse')
        ;
    }
}
