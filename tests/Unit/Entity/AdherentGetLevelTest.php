<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity;

use App\Adherent\AdherentLevel;
use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;

class AdherentGetLevelTest extends TestCase
{
    public function testNoTagReturnsContact(): void
    {
        self::assertSame(AdherentLevel::CONTACT, $this->withTags([])->getLevel());
    }

    public function testContactTagReturnsContact(): void
    {
        self::assertSame(AdherentLevel::CONTACT, $this->withTags([TagEnum::CONTACT])->getLevel());
    }

    public function testUserTagReturnsUser(): void
    {
        self::assertSame(AdherentLevel::USER, $this->withTags([TagEnum::USER])->getLevel());
    }

    public function testSympathizerMembreReturnsMembre(): void
    {
        self::assertSame(AdherentLevel::MEMBRE, $this->withTags([TagEnum::SYMPATHISANT_MEMBRE])->getLevel());
    }

    public function testOtherPartySympathizerReturnsMembre(): void
    {
        // Confirme la décision PO : tous les sympathisant:* sont MEMBRE (y compris autre_parti).
        self::assertSame(AdherentLevel::MEMBRE, $this->withTags([TagEnum::SYMPATHISANT_AUTRE_PARTI])->getLevel());
    }

    public function testAdherentTagReturnsAdherent(): void
    {
        self::assertSame(AdherentLevel::ADHERENT, $this->withTags([TagEnum::ADHERENT])->getLevel());
    }

    public function testActiveMembershipReturnsAdherentAJour(): void
    {
        $adherent = $this->withTags([TagEnum::ADHERENT, TagEnum::getAdherentYearTag()]);

        self::assertSame(AdherentLevel::ADHERENT_A_JOUR, $adherent->getLevel());
    }

    public function testActiveMembershipShortCircuitsAdherent(): void
    {
        $adherent = $this->withTags([TagEnum::ADHERENT, TagEnum::getAdherentYearTag()]);

        self::assertNotSame(AdherentLevel::ADHERENT, $adherent->getLevel());
    }

    /**
     * @param string[] $tags
     */
    private function withTags(array $tags): Adherent
    {
        $adherent = new Adherent();
        $adherent->tags = $tags;

        return $adherent;
    }
}
