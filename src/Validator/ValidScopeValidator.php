<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidScopeValidator extends ConstraintValidator
{
    private $authorizationChecker;
    protected $security;

    public function __construct(AuthorizationChecker $authorizationChecker, Security $security)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
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

        if (!$user instanceof Adherent || $this->authorizationChecker->isValidScopeForAdherent($value, $user)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
