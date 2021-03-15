<?php

namespace App\Validator\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\TerritorialCouncil\Candidacy\NationalCouncilCandidacyConfigurator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidTerritorialCouncilCandidacyInvitationValidator extends ConstraintValidator
{
    public function validate($candidacy, Constraint $constraint)
    {
        if (!$constraint instanceof ValidTerritorialCouncilCandidacyInvitation) {
            throw new UnexpectedTypeException($constraint, ValidTerritorialCouncilCandidacyInvitation::class);
        }

        if (null === $candidacy) {
            return;
        }

        if (!$candidacy instanceof Candidacy) {
            throw new UnexpectedValueException($candidacy, Candidacy::class);
        }

        $gendersConfig = NationalCouncilCandidacyConfigurator::getAvailableGenders($candidacy);

        foreach ($candidacy->getInvitations() as $invitation) {
            if (0 > --$gendersConfig[$invitation->getMembership()->getAdherent()->getGender()]) {
                $this->context
                    ->buildViolation($constraint->messageInvalidGender)
                    ->atPath('membership')
                    ->addViolation()
                ;

                return;
            }
        }

        $configQualities = NationalCouncilCandidacyConfigurator::getNeededQualitiesForNationalCouncilDesignation();
        $foundCandidacy = [];

        foreach ($candidacy->getInvitations() as $invitation) {
            /** @var TerritorialCouncilMembership $invitedMembership */
            $invitedMembership = $invitation->getMembership();

            if ($invitedMembership->hasForbiddenForCandidacyQuality()) {
                $this->context
                    ->buildViolation($constraint->messageMembershipNotAvailable)
                    ->atPath('invitations')
                    ->addViolation()
                ;

                return;
            }

            $invitedCandidacy = $invitedMembership->getCandidacyForElection($candidacy->getElection());

            if ($invitedCandidacy && $invitedCandidacy->isConfirmed()) {
                $this->context
                    ->buildViolation($constraint->messageMembershipAlreadyCandidate)
                    ->atPath('invitations')
                    ->addViolation()
                ;

                return;
            }

            foreach ($configQualities as $index => $allowedQualities) {
                if (array_intersect($allowedQualities, $invitedMembership->getQualityNames())) {
                    if (\in_array($index, $foundCandidacy)) {
                        $this->context
                            ->buildViolation($constraint->messageInvalidQuality)
                            ->atPath('invitations')
                            ->addViolation()
                        ;

                        return;
                    }

                    $foundCandidacy[] = $index;

                    continue 2;
                }
            }

            if (3 !== \count($foundCandidacy)) {
                $this->context
                    ->buildViolation($constraint->messageInvalidParity)
                    ->atPath('invitations')
                    ->addViolation()
                ;

                return;
            }
        }
    }
}
