<?php

declare(strict_types=1);

namespace App\OAuth\Repository;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

class OAuthUserRepository implements IdentityProviderInterface
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function getUserEntityByIdentifier($identifier): ?Adherent
    {
        return $this->adherentRepository->findOneByUuid($identifier);
    }
}
