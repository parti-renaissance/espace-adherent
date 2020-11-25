<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManagedZoneValidator extends ConstraintValidator
{
    /**
     * @var ManagedZoneProvider
     */
    private $managedZoneProvider;

    /**
     * @var TokenStorageInterface
     */
    private $security;

    public function __construct(ManagedZoneProvider $managedZoneProvider, Security $security)
    {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManagedZone) {
            throw new UnexpectedTypeException($constraint, ManagedZone::class);
        }

        if (null === $value) {
            return;
        }

        $zones = $this->valueAsZones($value);

        $user = $this->getAuthenticatedUser();
        if (!$user) {
            throw new \InvalidArgumentException('No user provided');
        }

        $managedZones = $this->managedZoneProvider->getManagedZones($user, $constraint->spaceType);
        $managedZonesIds = array_map(static function (Zone $zone) {
            return $zone->getId();
        }, $managedZones);

        foreach ($zones as $zone) {
            if (!$this->zoneBelongsToSome($zone, $managedZonesIds)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation()
                ;

                break;
            }
        }
    }

    /**
     * @return Zone[]
     */
    private function valueAsZones($value): array
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

    /**
     * @param Zone[] $managedIds
     */
    private function zoneBelongsToSome(Zone $zone, array $managedIds): bool
    {
        $ids = array_map(static function (Zone $zone): int {
            return $zone->getId();
        }, $zone->getParents());

        $ids[] = $zone->getId();

        $intersect = array_intersect($ids, $managedIds);

        return \count($intersect) > 0;
    }
}
