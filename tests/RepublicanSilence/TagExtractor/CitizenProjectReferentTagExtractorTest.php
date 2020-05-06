<?php

namespace Tests\App\RepublicanSilence\TagExtractor;

use App\Collection\CitizenProjectMembershipCollection;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectMembership;
use App\RepublicanSilence\TagExtractor\CitizenProjectReferentTagExtractor;
use PHPUnit\Framework\TestCase;

class CitizenProjectReferentTagExtractorTest extends TestCase
{
    public function testExtractTags()
    {
        $tagExtractor = new CitizenProjectReferentTagExtractor();

        $adherentMock = $this->createConfiguredMock(Adherent::class, [
            'getCitizenProjectMemberships' => new CitizenProjectMembershipCollection([
                $this->createConfiguredMock(CitizenProjectMembership::class, [
                    'isAdministrator' => true,
                    'getCitizenProject' => $this->createConfiguredMock(CitizenProject::class, [
                        'getSlug' => 'project-slug',
                        'getPostalCode' => '75001',
                        'getCityName' => 'Paris',
                        'getCountryName' => 'France',
                        'getCountry' => 'FR',
                    ]),
                ]),
            ]),
        ]);

        $this->assertSame(
            ['75001', 'Paris', 'France', 'FR', '75'],
            $tagExtractor->extractTags($adherentMock, 'project-slug')
        );
    }
}
