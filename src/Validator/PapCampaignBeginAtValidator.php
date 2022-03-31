<?php

namespace App\Validator;

use App\Entity\Pap\Campaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PapCampaignBeginAtValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PapCampaignBeginAt) {
            throw new UnexpectedTypeException($constraint, PapCampaignBeginAt::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof Campaign) {
            throw new UnexpectedValueException($value, Campaign::class);
        }

        $oldObject = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value);
        if ($oldObject
            && $oldObject['beginAt'] < new \DateTime()
            && $oldObject['beginAt'] !== $value->getBeginAt()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
