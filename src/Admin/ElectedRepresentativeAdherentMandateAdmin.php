<?php

declare(strict_types=1);

namespace App\Admin;

use App\Adherent\MandateTypeEnum;
use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class ElectedRepresentativeAdherentMandateAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.elected_representative_adherent_mandate';

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'type';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zone', ModelAutocompleteType::class, [
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
        string $value,
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
        $mandateType = $request->query->get('mandate_type');

        if ($mandateType && $zoneTypeConditions = (MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$mandateType] ?? [])) {
            $conditions = [];

            if (\array_key_exists('types', $zoneTypeConditions)) {
                $conditions[] = \sprintf('%s.type IN (:zone_types)', $alias);
                $qb->setParameter('zone_types', $zoneTypeConditions['types']);
            }

            if (\array_key_exists('codes', $zoneTypeConditions)) {
                $conditions[] = \sprintf('%s.code IN (:zone_codes)', $alias);
                $qb->setParameter('zone_codes', $zoneTypeConditions['codes']);
            }

            $qb->andWhere($qb->expr()->andX(...$conditions));
        }

        $qb->andWhere(\sprintf('%s.active = 1', $alias));
    }
}
