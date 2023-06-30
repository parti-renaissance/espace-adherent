<?php

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class TerritorialCouncilControllerCaseTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private $territorialCouncilRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->territorialCouncilRepository = $this->getTerritorialCouncilRepository();
    }

    protected function tearDown(): void
    {
        $this->territorialCouncilRepository = null;

        parent::tearDown();
    }

    public function testCannotChangePoliticalCommitteeMembershipIfNotValidAction(): void
    {
        $territorialCouncil = $this->territorialCouncilRepository->findOneBy(['codes' => '75']);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->authenticateAsAdmin($this->client);

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorialcouncil/%s/members/%s/%s-membership', $territorialCouncil->getId(), $adherent->getId(), 'invalid_action')
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    #[DataProvider('provideActions')]
    public function testCannotChangePoliticalCommitteeMembershipIfNotValidToken(string $action): void
    {
        $territorialCouncil = $this->territorialCouncilRepository->findOneBy(['codes' => '75']);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->authenticateAsAdmin($this->client);

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorialcouncil/%s/members/%s/%s-membership', $territorialCouncil->getId(), $adherent->getId(), $action)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public static function provideActions(): iterable
    {
        yield [PoliticalCommitteeManager::CREATE_ACTION];
        yield [PoliticalCommitteeManager::REMOVE_ACTION];
    }
}
