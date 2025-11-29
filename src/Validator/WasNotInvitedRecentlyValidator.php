<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Invite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WasNotInvitedRecentlyValidator extends ConstraintValidator
{
    private $invitationRepository;
    private $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->invitationRepository = $entityManager->getRepository(Invite::class);
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || !$constraint instanceof WasNotInvitedRecently) {
            return;
        }

        $email = $this->propertyAccessor->getValue($value, $constraint->emailField);

        if (!$email) {
            return;
        }

        if ($this->invitationRepository->hasEmailAlreadyBeenInvitedRecently($email, $constraint->since)) {
            $this->context->addViolation($constraint->message, ['{{ email }}' => $email]);
        }
    }
}
