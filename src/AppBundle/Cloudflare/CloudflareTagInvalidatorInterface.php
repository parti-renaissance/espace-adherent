<?php

namespace AppBundle\Cloudflare;

interface CloudflareTagInvalidatorInterface
{
    /**
     * Invalidate a given list of tags.
     *
     * @param string[] $tags
     */
    public function invalidateTags(array $tags);
}
