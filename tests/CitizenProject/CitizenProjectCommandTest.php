<?php

namespace Tests\App\CitizenProject;

use App\Address\NullableAddress;
use App\CitizenProject\CitizenProjectUpdateCommand;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectCategory;
use App\Entity\CitizenProjectSkill;
use App\Entity\Committee;
use App\Entity\NullablePostAddress;
use Doctrine\Common\Collections\Collection;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group citizenProject
 */
class CitizenProjectCommandTest extends TestCase
{
    const CREATOR_UUID = '0214e826-0116-4caa-a635-3f6f51a86750';

    public function testCreateCitizenProjectCommandFromCitizenProject()
    {
        $name = 'Projet citoyen à Lyon 1er';
        $subtitle = 'Le projet citoyen à Lyon 1er';
        $uuid = Uuid::uuid4();
        $citizenProjectCategory = $this->createMock(CitizenProjectCategory::class);
        $committee = $this->createMock(Committee::class);
        $problemDescription = 'Problem description';
        $proposedSolution = 'Proposed solution';
        $requiredMeans = 'Required means';
        $skill = $this->createMock(CitizenProjectSkill::class);

        $citizenProject = new CitizenProject(
            $uuid,
            Uuid::fromString(self::CREATOR_UUID),
            $name,
            $subtitle,
            $citizenProjectCategory,
            [$committee],
            $problemDescription,
            $proposedSolution,
            $requiredMeans,
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            null,
            null,
            '69001-en-marche-lyon'
        );
        $citizenProject->setSkills([$skill]);

        $citizenProjectUpdateCommand = CitizenProjectUpdateCommand::createFromCitizenProject($citizenProject);

        $this->assertInstanceOf(CitizenProjectUpdateCommand::class, $citizenProjectUpdateCommand);
        $this->assertSame($uuid, $citizenProjectUpdateCommand->getCitizenProjectUuid());
        $this->assertSame($citizenProject, $citizenProjectUpdateCommand->getCitizenProject());
        $this->assertSame($name, $citizenProjectUpdateCommand->name);
        $this->assertSame($citizenProjectCategory, $citizenProjectUpdateCommand->getCategory());
        $this->assertCount(1, $citizenProjectUpdateCommand->getCommitteeSupports()->toArray());
        $this->assertSame($problemDescription, $citizenProjectUpdateCommand->getProblemDescription());
        $this->assertSame($proposedSolution, $citizenProjectUpdateCommand->getProposedSolution());
        $this->assertSame($requiredMeans, $citizenProjectUpdateCommand->getRequiredMeans());
        $this->assertInstanceOf(Collection::class, $citizenProject->getSkills());
        $this->assertCount(1, $citizenProjectUpdateCommand->getSkills());
        $this->assertInstanceOf(NullableAddress::class, $citizenProjectUpdateCommand->getAddress());
        $this->assertNull($citizenProject->getDistrict());
        $this->assertNull($citizenProject->getTurnkeyProject());
    }
}
