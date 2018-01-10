<?php

namespace AppBundle\Timeline;

use AppBundle\Algolia\ManualIndexerInterface;
use AppBundle\Entity\Timeline\Profile;

class ProfileManager
{
    private $algolia;

    public function __construct(ManualIndexerInterface $algolia)
    {
        $this->algolia = $algolia;
    }

    public function postPersist(Profile $profile): void
    {
        $this->index($profile);
    }

    public function postUpdate(Profile $profile): void
    {
        $this->index($profile);
    }

    public function postRemove(Profile $profile): void
    {
        $this->unIndex($profile);
    }

    private function index(Profile $profile): void
    {
        $this->algolia->index($profile);
    }

    private function unIndex(Profile $profile): void
    {
        $this->algolia->unIndex($profile);
    }
}
