<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image;

use App\SocialNetwork\Image\Storage\PublishedImage;

/**
 * Shared publishing step for feed image handlers.
 *
 * The using class must expose `$this->publisher` (FeedImagePublisherInterface) and
 * `$this->logger` (Psr\Log\LoggerInterface).
 */
trait PublishesFeedImageTrait
{
    /**
     * Publishes $source to the public bucket and hands the result to $set, unless the current value
     * already matches the expected (deterministic) path. A permanent failure is logged and swallowed;
     * a transient failure bubbles up to trigger a transport retry.
     *
     * @param callable(PublishedImage): void $set
     */
    private function publishSource(?string $source, ?string $current, callable $set): void
    {
        if (null === $source) {
            return;
        }

        if ($current === $this->publisher->expectedPath($source)) {
            return;
        }

        try {
            $set($this->publisher->publish($source));
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error('[Feed image] permanent publish failure.', ['source' => $source, 'error' => $exception->getMessage()]);
        }
    }
}
