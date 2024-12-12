<?php

namespace App\Validator;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\AdherentZoneBasedRole;
use App\Scope\ScopeEnum;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ZoneBasedRolesValidator extends ConstraintValidator
{
    private const LIMITS = [
        ScopeEnum::LEGISLATIVE_CANDIDATE => 1,
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ZoneBasedRoles) {
            throw new UnexpectedTypeException($constraint, ZoneBasedRoles::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedValueException($value, Collection::class);
        }

        if (!$value->count()) {
            return;
        }

        $types = array_map(function (AdherentZoneBasedRole $role) {
            return $role->getType();
        }, $roles = $value->toArray());

        if (max(array_count_values($types)) > 1) {
            $this->context
                ->buildViolation($constraint->duplicateRoleTypeMessage)
                ->addViolation()
            ;

            return;
        }

        /** @var AdherentZoneBasedRole[] $roles */
        foreach ($roles as $key => $role) {
            if (!$role->getType()) {
                continue;
            }

            if (!$allowedTypesConfiguration = ZoneBasedRoleTypeEnum::ZONE_TYPE_CONDITIONS[$role->getType()] ?? null) {
                continue;
            }

            if ($role->getZones()->isEmpty()) {
                $this->context
                    ->buildViolation($constraint->emptyZoneMessage)
                    ->atPath('['.$key.'].zones')
                    ->setParameter('{{role_type}}', $role->getType())
                    ->addViolation()
                ;

                continue;
            }

            if (
                isset(self::LIMITS[$role->getType()])
                && $role->getZones()->count() > self::LIMITS[$role->getType()]
            ) {
                $this->context
                    ->buildViolation($constraint->limitZoneMessage)
                    ->atPath('['.$key.'].zones')
                    ->setParameter('{{limit}}', self::LIMITS[$role->getType()])
                    ->addViolation()
                ;

                continue;
            }

            $allowedTypes = [];
            foreach ($allowedTypesConfiguration as $typeKey => $value) {
                $allowedTypes[] = is_numeric($typeKey) ? $value : $typeKey;
            }

            foreach ($role->getZones() as $zone) {
                if (
                    !\in_array($zone->getType(), $allowedTypes, true)
                    || (
                        !empty($allowedTypesConfiguration[$zone->getType()])
                        && !\in_array($zone->getCode(), $allowedTypesConfiguration[$zone->getType()], true)
                    )
                ) {
                    $this->context
                        ->buildViolation($constraint->invalidZoneTypeMessage)
                        ->atPath('['.$key.'].zones')
                        ->setParameter('{{zone_type}}', $zone->getType())
                        ->setParameter('{{zone_code}}', $zone->getCode())
                        ->setParameter('{{role_type}}', $role->getType())
                        ->addViolation()
                    ;

                    return;
                }
            }
        }
    }
}
