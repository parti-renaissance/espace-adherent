<?php

namespace App\Validator\Jecoute;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReferentNewsValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param News $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReferentNews) {
            throw new UnexpectedTypeException($constraint, ReferentNews::class);
        }

        $user = $this->security->getUser();

        if (!$user instanceof Adherent || JecouteSpaceEnum::REFERENT_SPACE !== $value->getSpace()) {
            return;
        }

        $zone = $value->getZone();

        if (null === $zone) {
            return;
        }

        if (!\in_array($zone->getType(), [Zone::BOROUGH, Zone::DEPARTMENT, Zone::REGION], true)) {
            $this->context->buildViolation($constraint->invalidZoneType)
                ->atPath('zone')
                ->addViolation()
            ;
        }
    }
}
