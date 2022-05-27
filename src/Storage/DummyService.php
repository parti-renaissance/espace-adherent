<?php

namespace App\Storage;

/**
 * This service is here so that the injected services
 * are not removed from the container after compilation.
 *
 * To be removed.
 */
class DummyService
{
    private StorageInterface $publicStorage;
    private StorageInterface $privateStorage;

    public function __construct(StorageInterface $publicStorage, StorageInterface $privateStorage)
    {
        $this->publicStorage = $publicStorage;
        $this->privateStorage = $privateStorage;
    }
}
