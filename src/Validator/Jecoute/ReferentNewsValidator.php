<?php

namespace App\Validator\Jecoute;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Geo\ManagedZoneProvider;
use App\Jecoute\JecouteSpaceEnum;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReferentNewsValidator extends ConstraintValidator
{
    private Security $security;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(Security $security, ManagedZoneProvider $managedZoneProvider)
    {
        $this->security = $security;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    /**
     * @param News $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ReferentNews) {
            throw new UnexpectedTypeException($constraint, ReferentNews::class);
        }

        $user = $this->security->getUser();

        if (!$user instanceof Adherent || JecouteSpaceEnum::REFERENT_SPACE !== $value->getSpace()) {
            return;
        }

        if (null === $value->getZone()) {
            $this->context->buildViolation($constraint->zoneNotNull)
                ->atPath('zone')
                ->addViolation()
            ;

            return;
        }

        if (!\in_array($value->getZone()->getType(), [Zone::BOROUGH, Zone::DEPARTMENT, Zone::REGION], true)) {
            $this->context->buildViolation($constraint->invalidZoneType)
                ->atPath('zone')
                ->addViolation()
            ;

            return;
        }

        $managedZonesIds = $this->managedZoneProvider->getManagedZonesIds($user, 'referent');

        if (!$this->managedZoneProvider->zoneBelongsToSome($value->getZone(), $managedZonesIds)) {
            $this->context->buildViolation($constraint->invalidManagedZone)
                ->atPath('zone')
                ->addViolation()
            ;
        }
    }
}
