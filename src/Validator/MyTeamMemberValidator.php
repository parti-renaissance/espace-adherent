<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MyTeamMemberValidator extends ConstraintValidator
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MyTeamMember) {
            throw new UnexpectedTypeException($constraint, MyTeamMember::class);
        }

        if (!$value instanceof Adherent) {
            return;
        }

        if (null !== $value->getSource() && !$value->isJemengageUser() && !$value->isRenaissanceUser()) {
            $this
                ->context
                ->buildViolation($constraint->messageInvalidAdherentSource)
                ->addViolation()
            ;
        }

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return;
        }

        $currentUser = $scope->getCurrentUser();
        $delegator = $scope->getDelegator();

        if (
            ($currentUser && $value->equals($currentUser))
            || ($delegator && $value->equals($delegator))
        ) {
            $this
                ->context
                ->buildViolation($constraint->messageCurrentUser)
                ->addViolation()
            ;
        }
    }
}
