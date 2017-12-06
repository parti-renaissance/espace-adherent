<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\Committee;
use AppBundle\Entity\NullablePostAddress;
use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CitizenProjectCommandTest extends TestCase
{
    const CREATOR_UUID = '0214e826-0116-4caa-a635-3f6f51a86750';

    public function testCreateCitizenProjectCommandFromCitizenProject()
    {
        $name = 'Projet citoyen à Lyon 1er';
        $subtitle = 'Le projet citoyen à Lyon 1er';
        $uuid = CitizenProject::createUuid($name);
        $citizenProjectCategory = $this->createMock(CitizenProjectCategory::class);
        $committee = $this->createMock(Committee::class);
        $assistanceNeeded = false;
        $assistanceContent = null;
        $problemDescription = 'Problem description';
        $proposedSolution = 'Proposed solution';
        $requiredMeans = 'Required means';

        $citizenProject = new CitizenProject(
            $uuid,
            Uuid::fromString(self::CREATOR_UUID),
            $name,
            $subtitle,
            $citizenProjectCategory,
            [$committee],
            $assistanceNeeded,
            $assistanceContent,
            $problemDescription,
            $proposedSolution,
            $requiredMeans,
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            '69001-en-marche-lyon'
        );

        $citizenProjectCommand = CitizenProjectCommand::createFromCitizenProject($citizenProject);

        $this->assertInstanceOf(CitizenProjectCommand::class, $citizenProjectCommand);
        $this->assertSame($uuid, $citizenProjectCommand->getCitizenProjectUuid());
        $this->assertSame($citizenProject, $citizenProjectCommand->getCitizenProject());
        $this->assertSame($name, $citizenProjectCommand->name);
        $this->assertSame($citizenProjectCategory, $citizenProjectCommand->getCategory());
        $this->assertCount(1, $citizenProjectCommand->getCommitteeSupports()->toArray());
        $this->assertSame($assistanceNeeded, $citizenProjectCommand->isAssistanceNeeded());
        $this->assertSame($assistanceContent, $citizenProjectCommand->getAssistanceContent());
        $this->assertSame($problemDescription, $citizenProjectCommand->getProblemDescription());
        $this->assertSame($proposedSolution, $citizenProjectCommand->getProposedSolution());
        $this->assertSame($requiredMeans, $citizenProjectCommand->getRequiredMeans());
        $this->assertInstanceOf(NullableAddress::class, $citizenProjectCommand->getAddress());
    }
}
