<?php

namespace AppBundle\Validator;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidAdherentCoReferentValidator extends ConstraintValidator
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidAdherentCoReferent) {
            throw new UnexpectedTypeException($constraint, ValidAdherentCoReferent::class);
        }

        if (null === $value || !$value instanceof ReferentPersonLink || !$value->isCoReferent()) {
            return;
        }

        if (!$matchedAdherent = $value->getAdherent()) {
            $this->context->addViolation($constraint->messageInvalidAdherentEmail);
        } elseif (($referent = $matchedAdherent->getReferentOfReferentTeam()) && $referent !== $this->security->getUser()) {
            $this->context->addViolation($constraint->messageAdherentIsAlreadyCoReferent);
        }
    }
}
