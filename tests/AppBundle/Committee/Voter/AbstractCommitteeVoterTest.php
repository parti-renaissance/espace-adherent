<?php

namespace Tests\AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\AdherentFactory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractCommitteeVoterTest extends \PHPUnit_Framework_TestCase
{
    const ADHERENT_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9697';
    const ADHERENT_2_UUID = '91ed3e73-0384-4963-9159-3505c849fe39';
    const ADHERENT_3_UUID = '0984f9b5-f109-4593-a1c2-8e6a6778f217';

    /* @var CommitteeFactory */
    private $committeeFactory;

    /* @var AdherentFactory */
    private $adherentFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->committeeFactory = new CommitteeFactory();
        $this->adherentFactory = new AdherentFactory(new EncoderFactory([
            Adherent::class => new PlaintextPasswordEncoder(),
        ]));
    }

    protected function tearDown()
    {
        $this->adherentFactory = null;
        $this->committeeFactory = null;

        parent::tearDown();
    }

    protected function createAnonymousToken(): AnonymousToken
    {
        return new AnonymousToken('heah', $this->getMockBuilder(UserInterface::class)->getMock());
    }

    protected function createAuthenticatedToken(UserInterface $adherent): UsernamePasswordToken
    {
        return new UsernamePasswordToken($adherent, $adherent->getPassword(), 'users_db', $adherent->getRoles());
    }

    protected function createAdherent(string $uuid, string $email = null): Adherent
    {
        return $this->adherentFactory->createFromArray([
            'uuid' => $uuid,
            'email' => $email ?? 'pablo@picasso.tld',
            'password' => 'pa$$w0r4',
            'gender' => 'male',
            'first_name' => 'Pablo',
            'last_name' => 'Picasso',
            'address' => PostAddress::createForeignAddress('ES', '28001', 'Madrid', '50 Calle de Don Ramón de la Cruz'),
            'birthdate' => '1881-10-25',
            'position' => 'retired',
        ]);
    }

    protected function createCommittee(string $adherentUuid): Committee
    {
        $createdBy = Uuid::fromString($adherentUuid);

        return $this->committeeFactory->createFromArray([
            'name' => 'Committee A',
            'description' => 'The Committee A',
            'created_by' => $createdBy->toString(),
            'address' => PostAddress::createForeignAddress('BE', '1030', 'Bruxelles', '65 Rue des Coteaux'),
        ]);
    }
}
