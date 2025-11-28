<?php

declare(strict_types=1);

namespace App\Validator;

use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MyTeamMemberScopeFeaturesValidator extends ConstraintValidator
{
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MyTeamMemberScopeFeatures) {
            throw new UnexpectedTypeException($constraint, MyTeamMemberScopeFeatures::class);
        }

        if (!\is_array($value)) {
            return;
        }

        if (!($scope = $this->scopeGeneratorResolver->generate())
            || !($features = $scope->getFeatures())) {
            return;
        }

        if ([] !== array_diff($value, $features)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
