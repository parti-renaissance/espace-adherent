<?php

declare(strict_types=1);

namespace App\PublicId;

use App\Repository\NationalEvent\EventInscriptionRepository;

class MeetingInscriptionPublicIdGenerator extends AbstractPublicIdGenerator
{
    public const PATTERN = '^E[0-9]{6}$';
    public const REGEX = '#'.self::PATTERN.'#';

    public function __construct(public readonly EventInscriptionRepository $repository)
    {
    }

    protected function build(): string
    {
        return 'E'.$this->generateRandomBlock(6);
    }

    protected function getRepository(): PublicIdRepositoryInterface
    {
        return $this->repository;
    }
}
