<?php

namespace App\Admin;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class AdherentZoneBasedRoleAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.adherent_zone_based_role_admin';

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'type',
    ];

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('zones', ModelAutocompleteType::class, [
            'callback' => [$this, 'prepareAutocompleteFilterCallback'],
            'to_string_callback' => [$this, 'toStringCallback'],
            'property' => ['name', 'code'],
        ]);
    }

    public function toStringCallback(Zone $zone): string
    {
        return sprintf(
            '%s : %s (%s)',
            $this->trans('geo_zone.'.$zone->getType()),
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

        if ($roleType && $zoneTypes = (ZoneBasedRoleTypeEnum::ZONE_TYPES[$roleType] ?? [])) {
            $qb
                ->andWhere(sprintf('%1$s.type IN(:types) AND %1$s.active = 1', $alias))
                ->setParameter('types', $zoneTypes)
            ;
        }
    }
}
