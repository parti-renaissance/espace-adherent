<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

/**
 * Marker interface grouping every message of the video transcoding pipeline. It carries no behaviour;
 * its only purpose is to route the whole pipeline to the sync transport in the test environment with a
 * single routing entry (config/packages/test/messenger.php), instead of listing each command class.
 */
interface VideoTranscodingMessageInterface
{
}
