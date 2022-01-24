<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MyTeamMemberValidator extends ConstraintValidator
{
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ZoneRepository $zoneRepository;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver, ZoneRepository $zoneRepository)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->zoneRepository = $zoneRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MyTeamMember) {
            throw new UnexpectedTypeException($constraint, MyTeamMember::class);
        }

        if (!$value instanceof Adherent) {
            return;
        }

        if (null !== $value->getSource() && !$value->isJemengageUser()) {
            $this
                ->context
                ->buildViolation($constraint->messageInvalidAdherentSource)
                ->addViolation()
            ;
        }

        if (!($scope = $this->scopeGeneratorResolver->generate())
            || !($zones = $scope->getZones())) {
            return;
        }

        if (!$this->zoneRepository->isInZones($value->getZones()->toArray(), $zones)) {
            $this
                ->context
                ->buildViolation($constraint->messageInvalidAdherent)
                ->addViolation()
            ;
        }
    }
}
