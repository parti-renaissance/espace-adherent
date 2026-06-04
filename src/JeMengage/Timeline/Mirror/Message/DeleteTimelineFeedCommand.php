<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Message;

use App\Messenger\Message\UuidDefaultAsyncMessage;

/**
 * Asynchronously remove one document from the timeline_feed mirror.
 *
 * The timeline item UUID is carried as the message UUID (getUuid()), resolved synchronously at
 * preRemove time because the handler cannot reload a deleted entity.
 */
class DeleteTimelineFeedCommand extends UuidDefaultAsyncMessage
{
}
