<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Adherent;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidScopeValidator extends ConstraintValidator
{
    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(Security $security, ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function validate($value, Constraint $constraint): void
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
        if (!$scope || ($scope->getCode() !== $value && $scope->getDelegatorCode() !== $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
