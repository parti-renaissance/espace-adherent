<?php

namespace App\PublicId;

use App\Repository\AdherentRepository;

class AdherentPublicIdGenerator extends AbstractPublicIdGenerator
{
    public const string PATTERN = '^[0-9]{3}-[0-9]{3}$';
    public const string REGEX = '#'.self::PATTERN.'#';

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
