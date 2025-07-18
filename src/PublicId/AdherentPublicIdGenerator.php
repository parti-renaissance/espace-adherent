<?php

namespace App\PublicId;

use App\Repository\AdherentRepository;

class AdherentPublicIdGenerator extends AbstractPublicIdGenerator
{
    public function __construct(public readonly AdherentRepository $repository)
    {
    }

    protected function build(): string
    {
        return $this->generateRandomBlock(3).'-'.$this->generateRandomBlock(3);
    }

    protected function getRepository(): PublicIdRepositoryInterface
    {
        return $this->repository;
    }
}
