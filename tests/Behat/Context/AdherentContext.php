<?php

namespace Tests\App\Behat\Context;

use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use Behat\MinkExtension\Context\RawMinkContext;

class AdherentContext extends RawMinkContext
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    /**
     * @Given User :email should have :expectedCount subscription types
     */
    public function userShouldHaveSubscriptionTypes(string $email, int $expectedCount): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $actualCount = \count($adherent->getSubscriptionTypes());

        if ($actualCount !== $expectedCount) {
            $this->raiseException(\sprintf('I found %d subscription types instead of %d', $actualCount, $expectedCount));
        }
    }

    /**
     * @Given User :email should have zones :zones
     */
    public function userShouldHaveZones(string $email, string $zones): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $zones = array_map('trim', explode(',', $zones));

        $adherentZones = $adherent->getZones()->map(fn (Zone $zone) => $zone->getTypeCode())->toArray();

        if (array_diff($zones, $adherentZones) || array_diff($adherentZones, $zones)) {
            $this->raiseException(\sprintf('User %s has zones %s instead of %s', $email, implode(', ', $adherentZones), implode(', ', $zones)));
        }
    }

    private function raiseException(string $message): void
    {
        throw new \RuntimeException($message);
    }
}
