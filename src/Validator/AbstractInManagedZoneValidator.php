<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractInManagedZoneValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly Security $security,
    ) {
    }

    /**
     * @param ManagedZone $constraint
     */
    protected function validateZones(array $zones, Constraint $constraint, ?array $managedZones = null): void
    {
        if (!$user = $this->getAuthenticatedUser()) {
            throw new \InvalidArgumentException('No user provided');
        }

        if (null !== $managedZones) {
            $managedZonesIds = array_map(function (Zone $zone) {return $zone->getId(); }, $managedZones);
        } else {
            $managedZonesIds = $this->managedZoneProvider->getManagedZonesIds($user, $constraint->spaceType);
        }

        if (!$managedZonesIds) {
            return;
        }

        foreach ($zones as $zone) {
            if (!$this->managedZoneProvider->zoneBelongsToSome($zone, $managedZonesIds)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath($constraint->path ?? '')
                    ->addViolation()
                ;

                return;
            }
        }
    }

    /**
     * @return Zone[]
     */
    protected function valueAsZones($value): array
    {
        $zones = !\is_array($value) ? [$value] : $value;

        foreach ($zones as $zone) {
            if (!$zone instanceof Zone) {
                throw new \InvalidArgumentException('Wrong type');
            }
        }

        return $zones;
    }

    private function getAuthenticatedUser(): ?Adherent
    {
        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return null;
        }

        return $user;
    }
}
