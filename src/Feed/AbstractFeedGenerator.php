<?php

namespace App\Feed;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractFeedGenerator implements FeedGeneratorInterface
{
    /**
     * The feed locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(string $locale, int $ttl, UrlGeneratorInterface $urlGenerator)
    {
        $this->locale = $locale;
        $this->ttl = $ttl;
        $this->urlGenerator = $urlGenerator;
    }
}
