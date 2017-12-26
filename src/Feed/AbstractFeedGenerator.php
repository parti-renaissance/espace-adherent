<?php

namespace AppBundle\Feed;

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

    /**
     * @param string                $locale
     * @param int                   $ttl
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(string $locale, int $ttl, UrlGeneratorInterface $urlGenerator)
    {
        $this->locale = $locale;
        $this->ttl = $ttl;
        $this->urlGenerator = $urlGenerator;
    }
}
