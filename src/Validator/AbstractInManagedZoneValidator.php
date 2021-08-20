<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Geo\ManagedZoneProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractInManagedZoneValidator extends ConstraintValidator
{
    /**
     * @var ManagedZoneProvider
     */
    protected $managedZoneProvider;

    /**
     * @var TokenStorageInterface
     */
    protected $security;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(ManagedZoneProvider $managedZoneProvider, Security $security, SessionInterface $session)
    {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
        $this->session = $session;
    }

    protected function validateZones(array $zones, Constraint $constraint, array $managedZones = null): void
    {
        if (!$user = $this->getAuthenticatedUser()) {
            throw new \InvalidArgumentException('No user provided');
        }

        if ($managedZones) {
            $managedZonesIds = array_map(function (Zone $zone) {return $zone->getId(); }, $managedZones);
        } elseif (!$managedZonesIds = $this->managedZoneProvider->getManagedZonesIds($user, $constraint->spaceType)) {
            return;
        }

        foreach ($zones as $zone) {
            if (!$this->managedZoneProvider->zoneBelongsToSome($zone, $managedZonesIds)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath($constraint->path ?? null)
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

    protected function getAuthenticatedUser(): ?Adherent
    {
        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return null;
        }

        if (($delegatedAccessUuid = $this->session->get(DelegatedAccess::ATTRIBUTE_KEY)) && ($access = $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid))) {
            return $access->getDelegator();
        }

        return $user;
    }
}
