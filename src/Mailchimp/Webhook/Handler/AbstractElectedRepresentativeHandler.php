<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractElectedRepresentativeHandler extends AbstractHandler
{
    private ElectedRepresentativeRepository $repository;

    #[Required]
    public function setRepository(ElectedRepresentativeRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function support(string $type, string $listId): bool
    {
        return $listId === $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();
    }

    protected function findElectedRepresentatives(string $email): array
    {
        return $this->repository->findByEmail($email);
    }
}
