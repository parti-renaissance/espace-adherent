<?php

namespace App\Validator;

use App\Scope\ScopeEnum;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidScopeValidator extends ConstraintValidator
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ValidScope) {
            throw new UnexpectedTypeException($constraint, ValidScope::class);
        }

        if ((ScopeEnum::REFERENT === $value && !$this->authorizationChecker->isGranted('ROLE_REFERENT'))
            || (ScopeEnum::DEPUTY === $value && !$this->authorizationChecker->isGranted('ROLE_DEPUTY'))
            || (ScopeEnum::CANDIDATE === $value && !$this->authorizationChecker->isGranted('ROLE_CANDIDATE_REGIONAL_HEADED'))
            || (ScopeEnum::SENATOR === $value && !$this->authorizationChecker->isGranted('ROLE_SENATOR'))
            || (ScopeEnum::NATIONAL === $value && !$this->authorizationChecker->isGranted('ROLE_NATIONAL'))
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
