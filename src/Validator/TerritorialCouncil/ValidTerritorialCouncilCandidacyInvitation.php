<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidTerritorialCouncilCandidacyInvitation extends Constraint
{
    public $messageMembershipAlreadyCandidate = 'territorial_council.candidacy.invitation.membership_already_candidate';
    public $messageMembershipNotAvailable = 'territorial_council.candidacy.invitation.membership_not_available';
    public $messageInvalidQuality = 'territorial_council.candidacy.invitation.invalid_membership_quality';
    public $messageInvalidParity = 'territorial_council.candidacy.invitation.invalid_parity';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
