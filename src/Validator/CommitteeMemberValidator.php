<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\CommitteeMembershipRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CommitteeMemberValidator extends ConstraintValidator
{
    private $committeeMembershipRepository;
    private $security;

    public function __construct(CommitteeMembershipRepository $committeeMembershipRepository, Security $security)
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->security = $security;
    }

    /**
     * @param Committee       $value
     * @param CommitteeMember $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CommitteeMember) {
            throw new UnexpectedTypeException($constraint, CommitteeMember::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Committee) {
            throw new UnexpectedValueException($value, Committee::class);
        }

        $adherent = $this->security->getUser();
        if (!$adherent instanceof Adherent) {
            throw new UnexpectedValueException($adherent, Adherent::class);
        }

        if (!$this->committeeMembershipRepository->isAdherentInCommittee($adherent, $value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
