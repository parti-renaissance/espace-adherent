<?php

namespace AppBundle\Cloudflare;

use Symfony\Component\HttpFoundation\Response;

class Cloudflare
{
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
        $response->setSharedMaxAge(31536000); // 1 year

        if ($tags) {
            $response->headers->set('Cache-Tag', implode(',', $tags));
        }

        return $response;
    }
}
