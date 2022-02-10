<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidScopeValidator extends ConstraintValidator
{
    private AuthorizationChecker $authorizationChecker;
    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        AuthorizationChecker $authorizationChecker,
        Security $security,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ValidScope) {
            throw new UnexpectedTypeException($constraint, ValidScope::class);
        }

        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (
            ($scope && $scope->getCode() !== $value && $scope->getDelegatorCode() !== $value)
            || !$this->authorizationChecker->isScopeGranted($value, $user)
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
