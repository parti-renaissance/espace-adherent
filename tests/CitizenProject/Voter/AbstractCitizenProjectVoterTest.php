<?php

namespace Tests\AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\NullablePostAddress;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\AdherentFactory;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractCitizenProjectVoterTest extends TestCase
{
    const ADHERENT_1_UUID = '5b18eba2-5ff6-454a-9a5d-0acdee44f6d2';
    const ADHERENT_2_UUID = 'e04c83cf-b713-449c-8a3a-77d2db0a38b6';
    const ADHERENT_3_UUID = 'c226d6ad-a39f-42d1-9644-1084b4629448';

    const CATEGORY_1 = 'My Category 1';
    const CATEGORY_2 = 'My Category 2';

    /* @var CitizenProjectFactory */
    private $citizenProjectFactory;

    /* @var AdherentFactory */
    private $adherentFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->citizenProjectFactory = new CitizenProjectFactory();
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
        return new AnonymousToken('foo', $this->getMockBuilder(UserInterface::class)->getMock());
    }

    protected function createAuthenticatedToken(UserInterface $adherent): UsernamePasswordToken
    {
        return new UsernamePasswordToken($adherent, $adherent->getPassword(), 'users_db', $adherent->getRoles());
    }

    protected function createAdherentFromUuidAndEmail(string $uuid, string $email = null): Adherent
    {
        return $this->adherentFactory->createFromArray([
            'uuid' => $uuid,
            'email' => $email ?? 'paolo@maldini.it',
            'password' => 'passw0rd',
            'gender' => 'male',
            'first_name' => 'Paolo',
            'last_name' => 'Maldini',
            'address' => PostAddress::createForeignAddress('IT', '21040', 'Carnago', '25 Via Milanello'),
            'birthdate' => '1968-06-26',
            'position' => 'retired',
        ]);
    }

    protected function createCitizenProject(string $adherentUuid, CitizenProjectCategory $category): CitizenProject
    {
        $createdBy = Uuid::fromString($adherentUuid);

        return $this->citizenProjectFactory->createFromArray([
            'uuid' => $createdBy,
            'name' => 'Citizen Project A',
            'subtitle' => 'Citizen Subtitle A',
            'description' => 'The Citizen Project A',
            'category' => $category,
            'problem_description' => 'The problem description A',
            'proposed_solution' => 'The proposed solution A',
            'required_means' => 'The required means A',
            'created_by' => $createdBy->toString(),
            'address' => NullablePostAddress ::createForeignAddress('IT', '20151', 'Milano', 'Piazzale Angelo Moratti'),
            'phone' => '33 320202020',
        ]);
    }
}
