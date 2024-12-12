<?php

namespace App\Validator;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Security\Voter\AdherentMessageTypeVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidAuthorRoleMessageTypeValidator extends ConstraintValidator
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ValidAuthorRoleMessageType) {
            throw new UnexpectedTypeException($constraint, ValidAuthorRoleMessageType::class);
        }

        if (!$value instanceof AdherentMessageInterface) {
            throw new UnexpectedValueException($value, AdherentMessageInterface::class);
        }

        if (!$this->authorizationChecker->isGranted(AdherentMessageTypeVoter::USER_CAN_EDIT_MESSAGE_TYPE, $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('type')
                ->addViolation()
            ;
        }
    }
}
