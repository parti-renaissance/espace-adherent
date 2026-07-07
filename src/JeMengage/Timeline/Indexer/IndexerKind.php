<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\JeMengage\Timeline\TimelineFeedTypeEnum;

/**
 * The six kinds the external indexer accepts (call-indexer.txt). Single source of truth for the
 * internal-type -> indexer-kind mapping and, by extension, the push gate: a type with no mapping
 * (transactional_message, riposte, survey, pap/phoning campaigns) is not pushable.
 *
 * The indexer does not validate kind server-side, so the value must always be one of these.
 */
enum IndexerKind: string
{
    case SOCIAL_POST = 'social_post';
    case EVENT = 'event';
    case ACTION = 'action';
    case PUBLICATION = 'publication';
    case NOTIFICATION = 'notification';
    case POLL = 'poll';

    public static function fromInternalType(string $internalType): ?self
    {
        return match ($internalType) {
            TimelineFeedTypeEnum::EVENT => self::EVENT,
            TimelineFeedTypeEnum::ACTION => self::ACTION,
            TimelineFeedTypeEnum::SOCIAL_NETWORK_POST => self::SOCIAL_POST,
            TimelineFeedTypeEnum::PUBLICATION => self::PUBLICATION,
            TimelineFeedTypeEnum::NEWS => self::NOTIFICATION,
            TimelineFeedTypeEnum::POLL => self::POLL,
            default => null,
        };
    }
}
