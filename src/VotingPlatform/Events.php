<?php

declare(strict_types=1);

namespace App\VotingPlatform;

final class Events
{
    public const CANDIDACY_CREATED = 'candidacy.created';
    public const CANDIDACY_UPDATED = 'candidacy.updated';
    public const CANDIDACY_REMOVED = 'candidacy.removed';

    public const CANDIDACY_INVITATION_UPDATE = 'candidacy.invitation.update';
    public const CANDIDACY_INVITATION_DECLINE = 'candidacy.invitation.decline';
    public const CANDIDACY_INVITATION_ACCEPT = 'candidacy.invitation.accept';
    public const CANDIDACY_INVITATION_REMOVE = 'candidacy.invitation.remove';
}
