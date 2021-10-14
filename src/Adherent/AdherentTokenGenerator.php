<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\AdherentEmailSubscribeToken;
use Doctrine\ORM\EntityManagerInterface;

class AdherentTokenGenerator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateEmailSubscriptionToken(
        Adherent $adherent,
        string $triggerSource
    ): AdherentEmailSubscribeToken {
        foreach ($this->entityManager->getRepository(AdherentEmailSubscribeToken::class)->findAllAvailable($adherent) as $token) {
            $token->invalidate();
        }

        $this->entityManager->persist($token = AdherentEmailSubscribeToken::generate($adherent, AdherentEmailSubscribeToken::DURATION));
        $token->setTriggerSource($triggerSource);

        $this->entityManager->flush();

        return $token;
    }
}
