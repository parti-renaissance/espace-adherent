<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidTerritorialCouncilCandidacyInvitation extends Constraint
{
    public $messageInvalidGender = 'territorial_council.candidacy.invitation.invalid_gender';
    public $messageMembershipAlreadyCandidate = 'territorial_council.candidacy.invitation.membership_already_candidate';
    public $messageMembershipNotAvailable = 'territorial_council.candidacy.invitation.membership_not_available';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
