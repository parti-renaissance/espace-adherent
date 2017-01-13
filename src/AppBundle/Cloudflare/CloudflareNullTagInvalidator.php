<?php

namespace AppBundle\Cloudflare;

use Psr\Log\LoggerInterface;

class CloudflareNullTagInvalidator implements CloudflareTagInvalidatorInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function invalidateTags(array $tags)
    {
        if ($this->logger) {
            $this->logger->info('[cloudflare] invalidating tags', [
                'tags' => $tags,
            ]);
        }
    }
}
