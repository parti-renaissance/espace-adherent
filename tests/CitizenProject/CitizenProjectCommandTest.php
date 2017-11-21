<?php

namespace Tests\AppBundle\CitizenProject;

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
        $description = 'Le projet citoyen à Lyon 1er';
        $uuid = CitizenProject::createUuid($name);

        $citizenProject = new CitizenProject(
            $uuid,
            Uuid::fromString(self::CREATOR_UUID),
            $name,
            $description,
            NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69001-en-marche-lyon'
        );

        $citizenProjectCommand = CitizenProjectCommand::createFromCitizenProject($citizenProject);

        $this->assertInstanceOf(CitizenProjectCommand::class, $citizenProjectCommand);
        $this->assertSame($uuid, $citizenProjectCommand->getCitizenProjectUuid());
        $this->assertSame($citizenProject, $citizenProjectCommand->getCitizenProject());
        $this->assertSame($name, $citizenProjectCommand->name);
        $this->assertSame($description, $citizenProjectCommand->description);
    }
}
