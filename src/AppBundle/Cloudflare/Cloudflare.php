<?php

namespace AppBundle\Cloudflare;

use Symfony\Component\HttpFoundation\Response;

class Cloudflare
{
    private $tagInvalidator;

    public function __construct(CloudflareTagInvalidatorInterface $tagInvalidator)
    {
        $this->tagInvalidator = $tagInvalidator;
    }

    /**
     * Cache a given response until it's purged from Cloudflare cache using one of the given tags.
     *
     * @param Response $response
     * @param array    $tags
     *
     * @return Response
     */
    public function cacheIndefinitely(Response $response, array $tags = [])
    {
        $response->setMaxAge(0);
        $response->setSharedMaxAge(31536000); // 1 year

        if ($tags) {
            $response->headers->set('Cache-Tag', implode(',', $tags));
        }

        return $response;
    }

    public function invalidateTag(string $tag)
    {
        $this->tagInvalidator->invalidateTags([$tag]);
    }

    /**
     * @param string[] $tags
     */
    public function invalidateTags(array $tags)
    {
        $this->tagInvalidator->invalidateTags($tags);
    }
}
