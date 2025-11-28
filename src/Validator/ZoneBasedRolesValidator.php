<?php

declare(strict_types=1);

namespace App\Validator;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\AdherentZoneBasedRole;
use App\Repository\AdherentZoneBasedRoleRepository;
use App\Scope\ScopeEnum;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZoneBasedRolesValidator extends ConstraintValidator
{
    private const LIMITS = [
        ScopeEnum::LEGISLATIVE_CANDIDATE => 1,
    ];

    private const TYPES_DUPLICATES_ALLOWED = [
        ScopeEnum::CORRESPONDENT,
        ScopeEnum::PROCURATIONS_MANAGER,
    ];

    public function __construct(
        private readonly AdherentZoneBasedRoleRepository $adherentZoneBasedRoleRepository,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

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

            $roleType = (isset(ScopeEnum::SCOPE_INSTANCES[$role->getType()]) ? ScopeEnum::SCOPE_INSTANCES[$role->getType()].' : ' : '').$this->translator->trans('role.'.$role->getType(), ['gender' => 'male']);

            if ($role->getZones()->isEmpty()) {
                $this->context
                    ->buildViolation($constraint->emptyZoneMessage)
                    ->atPath('['.$key.'].zones')
                    ->setParameter('{{role_type}}', $roleType)
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
                    ->setParameter('{{limit}}', (string) self::LIMITS[$role->getType()])
                    ->addViolation()
                ;

                continue;
            }

            $allowedTypes = [];
            foreach ($allowedTypesConfiguration as $typeKey => $v) {
                $allowedTypes[] = is_numeric($typeKey) ? $v : $typeKey;
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
                        ->setParameter('{{role_type}}', $roleType)
                        ->addViolation()
                    ;

                    return;
                }

                if (
                    !$role->isHidden()
                    && !\in_array($role->getType(), self::TYPES_DUPLICATES_ALLOWED, true)
                    && $zoneDuplicate = $this->adherentZoneBasedRoleRepository->findZoneDuplicate($role, $zone)
                ) {
                    $adherent = $zoneDuplicate->getAdherent();

                    $this->context
                        ->buildViolation($constraint->zoneDuplicateMessage)
                        ->atPath('['.$key.'].zones')
                        ->setParameter('{{zone_code}}', $zone->getCode())
                        ->setParameter('{{zone_name}}', $zone->getName())
                        ->setParameter('{{role_type}}', $roleType)
                        ->setParameter('{{adherent_full_name}}', $adherent->getFullName())
                        ->setParameter('{{adherent_public_id}}', $adherent->getPublicId())
                        ->setParameter('{{adherent_edit_url}}', $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $adherent->getId()]))
                        ->addViolation()
                    ;

                    return;
                }
            }
        }
    }
}
