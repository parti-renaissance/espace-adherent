<?php

namespace Tests\App\Behat\Context;

use App\Entity\Adherent;
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
        $adherent = $this->findAdherent($email);
        $actualCount = \count($adherent->getSubscriptionTypes());

        if ($actualCount !== $expectedCount) {
            $this->raiseException(\sprintf('I found %d subscription types instead of %d', $actualCount, $expectedCount));
        }
    }

    /**
     * @Given User :email should be in :committeeName committee
     */
    public function userShouldBeInCommittee(string $email, string $committeeName): void
    {
        $adherent = $this->findAdherent($email);

        if ($adherent->getCommitteeMembership()?->getCommittee()->getName() !== $committeeName) {
            $this->raiseException(\sprintf('User "%s" is not in "%s" committee, but in "%s"', $email, $committeeName, $adherent->getCommitteeMembership()?->getCommittee()->getName()));
        }
    }

    /**
     * @Given User :email should have zones :zones
     */
    public function userShouldHaveZones(string $email, string $zones): void
    {
        $adherent = $this->findAdherent($email);
        $zones = array_map('trim', explode(',', $zones));

        $adherentZones = $adherent->getZones()->map(fn (Zone $zone) => $zone->getTypeCode())->toArray();

        if (array_diff($zones, $adherentZones) || array_diff($adherentZones, $zones)) {
            $this->raiseException(\sprintf('User %s has zones %s instead of %s', $email, implode(', ', $adherentZones), implode(', ', $zones)));
        }
    }

    /**
     * @Given User :email should have tag :tag
     */
    public function userShouldHaveTag(string $email, string $tag): void
    {
        $adherent = $this->findAdherent($email);

        if (false === $adherent->hasTag($tag)) {
            $this->raiseException(\sprintf('User %s does not have tag %s (tags: %s)', $email, $tag, implode(', ', $adherent->tags)));
        }
    }

    private function findAdherent(string $email): Adherent
    {
        if (!$adherent = $this->adherentRepository->findOneByEmail($email)) {
            $this->raiseException(\sprintf('User with email %s not found', $email));
        }

        return $adherent;
    }

    private function raiseException(string $message): void
    {
        throw new \RuntimeException($message);
    }
}
