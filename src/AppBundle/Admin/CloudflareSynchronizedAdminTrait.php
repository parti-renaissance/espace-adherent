<?php

namespace AppBundle\Admin;

use AppBundle\Cloudflare\Cloudflare;

trait CloudflareSynchronizedAdminTrait
{
    /**
     * @var Cloudflare
     */
    private $cloudflare;

    /**
     * Invalid the given object in Cloudflare.
     *
     * @param $object
     */
    abstract public function invalidate($object);

    public function setCloudflare(Cloudflare $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    public function getCloudflare(): Cloudflare
    {
        return $this->cloudflare;
    }

    public function postUpdate($object)
    {
        $this->invalidate($object);
    }

    public function preRemove($object)
    {
        $this->invalidate($object);
    }
}
