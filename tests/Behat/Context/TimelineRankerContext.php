<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;

/**
 * Points the timeline ranker at a mock host whose get_items fixture returns a single, known item, so a
 * scenario can drive the default (indexer) read path instead of the Algolia fallback. TIMELINE_RANKER_URL
 * is captured and restored around every scenario so the override never leaks to the fallback scenarios.
 */
class TimelineRankerContext implements Context
{
    private const string POLL_RANKER_URL = 'https://ranker-poll.timeline.test';

    private ?string $originalRankerUrl = null;

    /**
     * @BeforeScenario
     */
    public function captureRankerUrl(): void
    {
        $this->originalRankerUrl = $_SERVER['TIMELINE_RANKER_URL'] ?? $_ENV['TIMELINE_RANKER_URL'] ?? null;
    }

    /**
     * @AfterScenario
     */
    public function restoreRankerUrl(): void
    {
        if (null === $this->originalRankerUrl) {
            unset($_SERVER['TIMELINE_RANKER_URL'], $_ENV['TIMELINE_RANKER_URL']);

            return;
        }

        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = $this->originalRankerUrl;
    }

    /**
     * @Given the timeline ranker returns the current poll
     */
    public function theTimelineRankerReturnsTheCurrentPoll(): void
    {
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::POLL_RANKER_URL;
    }
}
