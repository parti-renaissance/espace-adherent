<?php

namespace App\Admin;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class AdherentZoneBasedRoleAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.adherent_zone_based_role_admin';

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'type';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zones', ModelAutocompleteType::class, [
            'callback' => [$this, 'prepareAutocompleteFilterCallback'],
            'to_string_callback' => [$this, 'toStringCallback'],
            'property' => ['name', 'code'],
            'btn_add' => false,
        ]);
    }

    public function toStringCallback(Zone $zone): string
    {
        return \sprintf(
            '%s : %s (%s)',
            $this->getTranslator()->trans('geo_zone.'.$zone->getType()),
            $zone->getName(),
            $zone->getCode()
        );
    }

    public static function prepareAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value
    ): void {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }
        $qb->orWhere($orx);

        $request = $admin->getRequest();
        $roleType = $request->query->get('role_type');

        if ($roleType && $zoneTypeConditions = (ZoneBasedRoleTypeEnum::ZONE_TYPE_CONDITIONS[$roleType] ?? [])) {
            $conditions = [];
            foreach ($zoneTypeConditions as $key => $value) {
                if (is_numeric($key)) {
                    $conditions[] = \sprintf('%s.type = :type_%d', $alias, $key);
                    $qb->setParameter('type_'.$key, $value);
                } else {
                    $conditions[] = \sprintf('%1$s.type = :type_%2$s AND %1$s.code IN (:code_%2$s)', $alias, $key);
                    $qb->setParameter('type_'.$key, $key);
                    $qb->setParameter('code_'.$key, $value);
                }
            }

            $qb
                ->andWhere(\sprintf('%s.active = 1', $alias))
                ->andWhere($qb->expr()->orX(...$conditions))
            ;
        }
    }
}
