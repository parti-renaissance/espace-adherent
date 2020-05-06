<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\CommitteeMembershipRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
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
     * @param Committee       $committee
     * @param CommitteeMember $constraint
     */
    public function validate($committee, Constraint $constraint)
    {
        if (!$constraint instanceof CommitteeMember) {
            throw new UnexpectedTypeException($constraint, CommitteeMember::class);
        }

        if (null === $committee) {
            return;
        }

        if (!$committee instanceof Committee) {
            throw new UnexpectedTypeException($committee, Committee::class);
        }

        $adherent = $this->security->getUser();
        if (!$adherent instanceof Adherent) {
            throw new UnexpectedTypeException($adherent, Adherent::class);
        }

        if (!$this->committeeMembershipRepository->isAdherentInCommittee($adherent, $committee)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
