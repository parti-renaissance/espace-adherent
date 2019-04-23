<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\CitizenProject\CitizenProjectCreationCommand;
use AppBundle\CitizenProject\CitizenProjectFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectSkill;
use AppBundle\Entity\NullablePostAddress;
use AppBundle\Entity\PostAddress;
use AppBundle\Referent\ReferentTagManager;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * @group citizenProject
 */
class CitizenProjectFactoryTest extends TestCase
{
    public function testCreateCitizenProjectFromCitizenProjectCreationCommand()
    {
        $email = 'jean.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $name = 'Projet citoyen à Lyon 1er Lyon 1er';
        $subtitle = 'le projet citoyen à Lyon 1er';
        $address = NullableAddress::createFromAddress(NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'));
        $problemDescription = 'Problem description';
        $proposedSolution = 'Proposed solution';
        $requiredMeans = 'Required means';
        $skill = $this->createMock(CitizenProjectSkill::class);

        $adherent = Adherent::create(
            $uuid,
            $email,
            'password',
            'male',
            'Jean',
            'DUPONT',
            new \DateTime('1991-02-09'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );

        $command = CitizenProjectCreationCommand::createFromAdherent($adherent);
        $command->setAddress($address);
        $command->setPhone((new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080901'));
        $command->name = $name;
        $command->subtitle = $subtitle;
        $command->category = $this->createMock(CitizenProjectCategory::class);
        $command->problemDescription = $problemDescription;
        $command->proposedSolution = $proposedSolution;
        $command->requiredMeans = $requiredMeans;
        $command->setSkills([$skill]);

        $citizenProjectFactory = new CitizenProjectFactory(
            $this->createMock(ReferentTagManager::class)
        );
        $citizenProject = $citizenProjectFactory->createFromCitizenProjectCreationCommand($command);

        $this->assertInstanceOf(CitizenProject::class, $citizenProject);
        $this->assertSame($address->getAddress(), $citizenProject->getAddress());
        $this->assertSame($name, $citizenProject->getName());
        $this->assertSame($subtitle, $citizenProject->getSubtitle());
        $this->assertSame($adherent->getUuid()->toString(), $citizenProject->getCreatedBy());
        $this->assertSame($problemDescription, $citizenProject->getProblemDescription());
        $this->assertSame($proposedSolution, $citizenProject->getProposedSolution());
        $this->assertSame($requiredMeans, $citizenProject->getRequiredMeans());
        $this->assertSame([$skill], $citizenProject->getSkills()->toArray());
    }
}
