<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ApiControllerTestTrait;
use Tests\AppBundle\Controller\ControllerTestTrait;

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
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/api/committees/%s/candidacies', LoadAdherentData::COMMITTEE_4_UUID));
        $this->isSuccessful($response = $this->client->getResponse());

        $data = \json_decode($response->getContent(), true);

        $this->assertArrayHasKey('metadata', $data);

        self::assertSame([
            'total' => 1,
            'males' => 1,
            'females' => 0,
        ], $data['metadata']);

        $this->assertArrayHasKey('candidacies', $data);

        self::assertCount(1, $data['candidacies']);

        self::assertArraySubset([[
            'first_name' => 'Jacques',
            'last_name' => 'Picard',
        ]], $data['candidacies']);
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
