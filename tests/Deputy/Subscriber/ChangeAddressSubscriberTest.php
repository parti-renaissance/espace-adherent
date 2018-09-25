<?php

namespace Tests\AppBundle\Deputy\Subscriber;

use AppBundle\Deputy\Subscriber\ChangeAddressSubscriber;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Entity\ReferentTag;
use AppBundle\Entity\ReferentTaggableEntity;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Membership\AdherentAccountWasCreatedEvent;
use AppBundle\Membership\AdherentProfileWasUpdatedEvent;
use AppBundle\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ChangeAddressSubscriberTest extends TestCase
{
    private $manager;

    /* @var DistrictRepository */
    private $districtRepository;

    /* @var ChangeAddressSubscriber */
    private $subscriber;

    public function testOnAdherentAccountRegistrationCompletedSucceeds()
    {
        $adherent = $this->createAdherent();
        $referentTag = $this->createReferentTag();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        $this->manager->expects($this->once())->method('flush');
        $this->districtRepository->expects($this->once())->method('findDistrictReferentTagByCoordinates')->willReturn($referentTag);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentAccountWasCreatedEvent($adherent));

        $this->assertSame(1, $adherent->getReferentTags()->count());
    }

    public function testOnAdherentProfileUpdatedSuccessfully()
    {
        $adherent = $this->createAdherent();
        $referentTag = $this->createReferentTag();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        $this->manager->expects($this->once())->method('flush');
        $this->districtRepository->expects($this->once())->method('findDistrictReferentTagByCoordinates')->willReturn($referentTag);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentProfileWasUpdatedEvent($adherent));

        $this->assertSame(1, $adherent->getReferentTags()->count());
    }

    private function createAdherent(): Adherent
    {
        return new Adherent(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::EMPLOYED,
            PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', 48.869878, 2.332197)
        );
    }

    private function createReferentTag(): ReferentTag
    {
        return new ReferentTag('1ère circonscription, Paris', 'CIRCO_75001');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->districtRepository = $this->createMock(DistrictRepository::class);
        $this->manager->expects($this->once())->method('getRepository')->willReturn($this->districtRepository);
        $this->subscriber = new ChangeAddressSubscriber($this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->districtRepository = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
