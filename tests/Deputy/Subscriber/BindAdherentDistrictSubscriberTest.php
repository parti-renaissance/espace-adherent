<?php

namespace Tests\AppBundle\Deputy\Subscriber;

use AppBundle\Deputy\Subscriber\BindAdherentDistrictSubscriber;
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

class BindAdherentDistrictSubscriberTest extends TestCase
{
    private $manager;

    /* @var DistrictRepository */
    private $districtRepository;

    /* @var BindAdherentDistrictSubscriber */
    private $subscriber;

    /**
     * @dataProvider provideReferentTagHasCount
     */
    public function testOnAdherentAccountRegistrationCompletedSucceeds(array $referentTags)
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if ($count = \count($referentTags)) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictReferentTagByCoordinates')->willReturn($referentTags);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentAccountWasCreatedEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    /**
     * @dataProvider provideReferentTagHasCount
     */
    public function testOnAdherentProfileUpdatedSuccessfully(array $referentTags)
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(ReferentTaggableEntity::class, $adherent);
        $this->assertSame(0, $adherent->getReferentTags()->count());

        if (0 < $count = \count($referentTags)) {
            $this->manager->expects($this->once())->method('flush');
        }
        $this->districtRepository->expects($this->once())->method('findDistrictReferentTagByCoordinates')->willReturn($referentTags);
        $this->subscriber->updateReferentTagWithDistrict(new AdherentProfileWasUpdatedEvent($adherent));

        $this->assertSame($count, $adherent->getReferentTags()->count());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
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

    public function provideReferentTagHasCount(): array
    {
        $tag1 = new ReferentTag('1ère circonscription, Paris', 'CIRCO_75001');
        $tag2 = new ReferentTag('Alpes-Maritimes, 1ère circonscription (06-01)', 'CIRCO_06001');

        return [
            [[]],
            [[$tag1]],
            [[$tag1, $tag2]],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->districtRepository = $this->createMock(DistrictRepository::class);
        $this->manager->expects($this->once())->method('getRepository')->willReturn($this->districtRepository);
        $this->subscriber = new BindAdherentDistrictSubscriber($this->manager);
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->districtRepository = null;
        $this->subscriber = null;

        parent::tearDown();
    }
}
