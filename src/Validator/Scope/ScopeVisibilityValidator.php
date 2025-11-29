<?php

declare(strict_types=1);

namespace App\Validator\Scope;

use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ScopeVisibilityValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly ZoneRepository $zoneRepository,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /**
     * @param EntityScopeVisibilityInterface $value
     * @param ScopeVisibility|Constraint     $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ScopeVisibility) {
            throw new UnexpectedTypeException($constraint, ScopeVisibility::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof EntityScopeVisibilityInterface) {
            throw new UnexpectedValueException($value, EntityScopeVisibilityInterface::class);
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return;
        }

        if ($scope->isNational()) {
            if (!$value->isNationalVisibility()) {
                $this
                    ->context
                    ->buildViolation($constraint->nationalScopeWithZoneMessage)
                    ->atPath('zone')
                    ->addViolation()
                ;
            }

            return;
        }

        if ($value->isNationalVisibility()) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithoutZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;

            return;
        }

        if (!$scope->getZones()) {
            return;
        }

        if (
            $value instanceof EntityScopeVisibilityWithZoneInterface
            && !$this->managedZoneProvider->zoneBelongsToSomeZones($value->getZone(), $scope->getZones())
        ) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithUnmanagedZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;
        }

        if (
            $value instanceof EntityScopeVisibilityWithZonesInterface
            && !$this->zoneRepository->isInZones($value->getZones()->toArray(), $scope->getZones())
        ) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithUnmanagedZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;
        }
    }
}
