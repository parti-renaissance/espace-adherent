<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationAcceptedMessage extends AbstractVotingPlatformMessage
{
    public static function create(
        CandidacyInterface $invitedCandidacy,
        CandidacyInterface $candidate,
        Designation $designation,
        string $candidaciesListUrl,
        array $params = [],
    ): self {
        $invited = $invitedCandidacy->getAdherent();
        $emailTitle = self::getMailSubjectPrefix($designation);
        $candidateAdherent = $candidate->getAdherent();

        $message = new self(
            Uuid::uuid4(),
            $candidateAdherent->getEmailAddress(),
            $candidateAdherent->getFullName(),
            \sprintf('[%s] %s', $emailTitle, $emailSubTitle = self::createSubject($designation, $invitedCandidacy)),
            array_merge([
                'email_title' => $emailTitle,
                'email_sub_title' => $emailSubTitle,
                'election_type' => $designation->getType(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'page_url' => $candidaciesListUrl,
            ], $params),
            self::getRecipientParams($designation, $candidate, $invitedCandidacy)
        );

        if (DesignationTypeEnum::NATIONAL_COUNCIL !== $designation->getType()) {
            $message->addRecipient(
                $invited->getEmailAddress(),
                $invited->getFullName(),
                [
                    'candidate_first_name' => $invited->getFirstName(),
                    'binome_first_name' => $candidateAdherent->getFirstName(),
                    'binome_last_name' => $candidateAdherent->getLastName(),
                ]
            );
        } elseif ($candidate->isConfirmed()) {
            foreach ($candidate->getOtherCandidacies() as $candidacy) {
                /** @var CandidacyInterface $otherCandidate */
                $otherCandidate = current(array_filter($candidacy->getOtherCandidacies(), function (CandidacyInterface $candidacy) use ($candidate) {
                    return $candidacy !== $candidate;
                }));

                $message->addRecipient(
                    $candidacy->getAdherent()->getEmailAddress(),
                    $candidacy->getAdherent()->getFullName(),
                    [
                        'candidate_first_name' => $candidacy->getFirstName(),
                        'binome_first_name' => $candidate->getFirstName(),
                        'binome_last_name' => $candidate->getLastName(),
                        'binome_2_first_name' => $otherCandidate ? $otherCandidate->getFirstName() : '',
                        'binome_2_last_name' => $otherCandidate ? $otherCandidate->getLastName() : '',
                    ]
                );
            }
        }

        return $message;
    }

    private static function createSubject(Designation $designation, CandidacyInterface $candidacy): string
    {
        if (DesignationTypeEnum::NATIONAL_COUNCIL === $designation->getType()) {
            if ($candidacy->isConfirmed()) {
                return 'Félicitations, vous êtes candidat(e) en trinôme !';
            }

            return \sprintf('%s a accepté votre demande.', $candidacy->getFirstName());
        }

        return 'Félicitations, vous êtes candidat(e) en binôme !';
    }

    private static function getRecipientParams(
        Designation $designation,
        CandidacyInterface $candidate,
        CandidacyInterface $invited,
    ): array {
        if (DesignationTypeEnum::NATIONAL_COUNCIL === $designation->getType()) {
            $invitations = array_filter($candidate->getInvitations(), function (CandidacyInvitationInterface $candidacyInvitation) use ($invited) {
                return $invited->getMembership() !== $candidacyInvitation->getMembership();
            });

            /** @var CandidacyInvitationInterface $otherInvitation */
            $otherInvitation = current($invitations);

            return [
                'candidate_first_name' => $candidate->getFirstName(),
                'binome_first_name' => $invited->getFirstName(),
                'binome_last_name' => $invited->getLastName(),
                'binome_2_first_name' => $otherInvitation ? $otherInvitation->getMembership()->getAdherent()->getFirstName() : '',
                'binome_2_last_name' => $otherInvitation ? $otherInvitation->getMembership()->getAdherent()->getLastName() : '',
            ];
        }

        return [
            'candidate_first_name' => $candidate->getFirstName(),
            'binome_first_name' => $invited->getFirstName(),
            'binome_last_name' => $invited->getLastName(),
        ];
    }
}
