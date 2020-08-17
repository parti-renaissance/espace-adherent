<?php

namespace App\TerritorialCouncil;

final class Events
{
    public const CANDIDACY_INVITATION_UPDATE = 'candidacy.invitation.update';
    public const CANDIDACY_INVITATION_DECLINE = 'candidacy.invitation.decline';
    public const CANDIDACY_INVITATION_ACCEPT = 'candidacy.invitation.accept';

    public const TERRITORIAL_COUNCIL_MEMBERSHIP_CREAT = 'territorial_council.membership.creat';
    public const TERRITORIAL_COUNCIL_MEMBERSHIP_REMOVE = 'territorial_council.membership.remove';
}
