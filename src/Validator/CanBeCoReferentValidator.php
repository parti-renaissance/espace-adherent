<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CanBeCoReferentValidator extends ConstraintValidator
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CanBeCoReferent) {
            throw new UnexpectedTypeException($constraint, CanBeCoReferent::class);
        }

        if (!$value) {
            return;
        }

        /** @var Adherent $adherent */
        $adherent = $this->context->getObject()->getAdherent();
        if ($adherent && $adherent->isCoReferent() && $adherent->getReferentOfReferentTeam() !== $this->getAuthenticatedUser()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }

    private function getAuthenticatedUser(): ?Adherent
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return null;
        }

        return $user;
    }
}
