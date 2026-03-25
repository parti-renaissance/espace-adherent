<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractInManagedZoneValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @param Zone[]      $zones
     * @param Zone[]      $managedZones
     * @param ManagedZone $constraint
     */
    protected function validateZones(array $zones, Constraint $constraint, array $managedZones): void
    {
        if (!$this->getAuthenticatedUser()) {
            throw new \InvalidArgumentException('No user provided');
        }

        if (!$managedZones) {
            return;
        }

        foreach ($zones as $zone) {
            if (!$this->zoneRepository->isInZones([$zone], $managedZones)) {
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
