<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Event\EventVisibilityEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MilitantEventCreationValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MilitantEventCreation) {
            throw new UnexpectedTypeException($constraint, MilitantEventCreation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Event) {
            throw new UnexpectedValueException($value, Event::class);
        }

        if (ScopeEnum::MILITANT !== $value->getAuthorScope()) {
            return;
        }

        $adherent = $this->security->getUser();
        if ($adherent instanceof Adherent && !$this->generalScopeGenerator->isPureMilitant($adherent)) {
            $this->context->buildViolation($constraint->notPureMilitantMessage)->addViolation();
        }

        if (EventVisibilityEnum::PUBLIC !== $value->visibility || $value->hidden) {
            $this->context->buildViolation($constraint->visibilityMessage)->atPath('visibility')->addViolation();
        }
    }
}
