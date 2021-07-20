<?php

namespace App\Validator;

use App\Audience\AudienceHelper;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Security\Voter\Audience\ManageAudienceVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidMessageAudienceValidator extends ConstraintValidator
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

        if (!$constraint instanceof ValidMessageAudience) {
            throw new UnexpectedTypeException($constraint, ValidMessageAudience::class);
        }

        if (!$value instanceof AdherentMessageInterface) {
            throw new UnexpectedValueException($value, AdherentMessageInterface::class);
        }

        if (null === $value->getAudience()) {
            return;
        }

        $messageClass = AudienceHelper::getMessageClassName(\get_class($value->getAudience()));
        if (!$value instanceof $messageClass) {
            $this->context
                ->buildViolation($constraint->messageNotValidClass)
                ->atPath('audience')
                ->addViolation()
            ;
        }

        if (!$this->authorizationChecker->isGranted(ManageAudienceVoter::PERMISSION, $value->getAudience())) {
            $this->context
                ->buildViolation($constraint->messageNoRights)
                ->atPath('audience')
                ->addViolation()
            ;
        }
    }
}
