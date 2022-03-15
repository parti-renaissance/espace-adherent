<?php

namespace App\Firebase\DynamicLinks;

use Kreait\Firebase\Contract\DynamicLinks;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private DynamicLinks $dynamicLinks;
    private DynamicLinkFactory $factory;

    public function __construct(DynamicLinks $dynamicLinks, DynamicLinkFactory $factory)
    {
        $this->dynamicLinks = $dynamicLinks;
        $this->factory = $factory;
    }

    public function create(DynamicLinkObjectInterface $object): ?UriInterface
    {
        try {
            return $this->dynamicLinks->createShortLink($this->factory->create($object))->uri();
        } catch (\Throwable $e) {
            $this->logger->error('Firebase: failed dynamic link creation', ['exception' => $e->getMessage()]);
        }

        return null;
    }
}
