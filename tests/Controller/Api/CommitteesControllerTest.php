<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class CommitteesControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiApprovedCommittees()
    {
        $this->client->request(Request::METHOD_GET, '/api/committees');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertGreaterThanOrEqual(8, \count(\GuzzleHttp\json_decode($content, true)));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
    }

    public function testGetCommitteeCandidacyReturnsNothingIfNonMemberOrAnonymous(): void
    {
        $url = sprintf('/api/committees/%s/candidacies', LoadAdherentData::COMMITTEE_4_UUID);

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch');

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testGetCommitteeCandidaciesReturnGoodJson(): void
    {
        $this->authenticateAsAdherent($this->client, 'assesseur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/api/committees/%s/candidacies', LoadAdherentData::COMMITTEE_6_UUID));
        $this->isSuccessful($response = $this->client->getResponse());

        $data = \json_decode($response->getContent(), true);

        $this->assertArrayHasKey('metadata', $data);

        self::assertSame([
            'total' => 2,
            'males' => 1,
            'females' => 1,
        ], $data['metadata']);

        $this->assertArrayHasKey('candidacies', $data);

        self::assertCount(2, $data['candidacies']);

        self::assertArraySubset([
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Assesseur',
        ], $data['candidacies'][0]);

        self::assertArraySubset([
            'photo' => null,
            'gender' => 'female',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
        ], $data['candidacies'][1]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->init();
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
