<?php

namespace App\PublicId;

use App\Repository\NationalEvent\EventInscriptionRepository;

class MeetingInscriptionPublicIdGenerator extends AbstractPublicIdGenerator
{
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
