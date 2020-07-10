<?php

namespace App\VotingPlatform;

final class Events
{
    public const CANDIDACY_CREATED = 'candidacy.created';
    public const CANDIDACY_UPDATED = 'candidacy.updated';
    public const CANDIDACY_REMOVED = 'candidacy.removed';

    public const CANDIDACY_PERIOD_CLOSE = 'candidacy.period.close';

    public const VOTE_OPEN = 'vote.open';
}
