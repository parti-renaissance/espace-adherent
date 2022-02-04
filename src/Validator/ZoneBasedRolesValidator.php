<?php

namespace App\Validator;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\AdherentZoneBasedRole;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ZoneBasedRolesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
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
        foreach ($roles as $role) {
            if (!$role->getType()) {
                continue;
            }

            if (!$allowedTypes = ZoneBasedRoleTypeEnum::ZONE_TYPES[$role->getType()] ?? null) {
                continue;
            }

            if ($role->getZones()->isEmpty()) {
                $this->context
                    ->buildViolation($constraint->emptyZoneMessage)
                    ->setParameter('{{role_type}}', $role->getType())
                    ->addViolation()
                ;
            }

            foreach ($role->getZones() as $zone) {
                if (!\in_array($zone->getType(), $allowedTypes, true)) {
                    $this->context
                        ->buildViolation($constraint->invalidZoneTypeMessage)
                        ->setParameter('{{zone_type}}', $zone->getType())
                        ->setParameter('{{role_type}}', $role->getType())
                        ->addViolation()
                    ;

                    return;
                }
            }
        }
    }
}
