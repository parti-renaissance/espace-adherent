<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\OAuth\Client;
use Symfony\Bundle\SecurityBundle\Security;

class OAuthClientVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CHECK_OAUTH_CLIENT';

    public function __construct(private readonly Security $security)
    {
    }

    /**
     * @param AuthoredInterface $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Client $subject */
        foreach ($subject->getRequestedRoles() ?? [] as $role) {
            $roleParts = explode('|', $role, 2);
            if (!$this->security->isGranted($roleParts[0], $roleParts[1] ?? $adherent)) {
                return false;
            }
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Client;
    }
}
