<?php

namespace App\Validator\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
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
                    ->buildViolation($constraint->messageInvalidParity)
                    ->atPath('membership')
                    ->addViolation()
                ;

                return;
            }
        }

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
        }

        if (!$this->isValidateMembers($candidacy)) {
            $this->context
                ->buildViolation($constraint->messageInvalidQuality)
                ->atPath('invitations')
                ->addViolation()
            ;

            return;
        }
    }

    private function isValidateMembers(Candidacy $candidacy): bool
    {
        $qualitiesToCheck = array_filter(NationalCouncilCandidacyConfigurator::getNeededQualitiesForNationalCouncilDesignation(), function (array $qualities) use ($candidacy) {
            return !\in_array($candidacy->getQuality(), $qualities);
        });

        $simplifiedInvitations = array_map(function (CandidacyInvitation $invitation): array {
            return [
                'uuid' => $invitation->getUuid()->toString(),
                'qualities' => $invitation->getMembership()->getQualityNames(),
            ];
        }, $candidacy->getInvitations());

        return $this->validateMembers($simplifiedInvitations, $qualitiesToCheck);
    }

    private function validateMembers(array $invitations, array $qualitiesToCheck): bool
    {
        if (\count($invitations) <= 0 || \count($qualitiesToCheck) <= 0) {
            return true;
        }

        $invitationsCopy = $this->prepareInvitationsForValidation($invitations);
        $invitationToCheck = array_shift($invitationsCopy);

        if (0 === \count($invitationToCheck['qualities'])) {
            foreach ($invitations as $invitationOriginal) {
                if ($invitationOriginal['uuid'] === $invitationToCheck['uuid']) {
                    break;
                }
            }

            $invitationToCheck['qualities'] = $invitationOriginal['qualities'];
        }

        foreach ($qualitiesToCheck as $subQualities) {
            foreach ($subQualities as $quality) {
                if (\in_array($quality, $invitationToCheck['qualities'], true)) {
                    return $this->validateMembers(
                        array_filter($invitations, function (array $invitation) use ($invitationToCheck) {
                            return $invitation['uuid'] !== $invitationToCheck['uuid'];
                        }),
                        array_filter($qualitiesToCheck, function (array $qualities) use ($quality) {
                            return !\in_array($quality, $qualities);
                        })
                    );
                }
            }
        }

        return false;
    }

    private function prepareInvitationsForValidation(array $invitations): array
    {
        $qualitiesToDelete = array_keys(array_filter(
            array_count_values(array_merge(...array_column($invitations, 'qualities'))),
            function ($value) {
                return $value > 1;
            }
        ));

        array_walk($invitations, function (array &$invitation) use ($qualitiesToDelete) {
            $invitation['qualities'] = array_diff($invitation['qualities'], $qualitiesToDelete);
        });

        return $invitations;
    }
}
