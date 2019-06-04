<?php

namespace AppBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class WasNotInvitedRecentlyValidator extends ConstraintValidator
{
    private $invitationRepository;
    private $propertyAccessor;

    public function __construct(EntityManager $entityManager, PropertyAccessor $propertyAccessor)
    {
        $this->invitationRepository = $entityManager->getRepository('AppBundle:Invite');
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate($value, Constraint $constraint)
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
