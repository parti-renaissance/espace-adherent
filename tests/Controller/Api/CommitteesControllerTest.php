<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadCommitteeV1Data;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class CommitteesControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testGetCommitteeCandidacyReturnsNothingIfNonMemberOrAnonymous(): void
    {
        $url = \sprintf('/api/committees/%s/candidacies', LoadCommitteeV1Data::COMMITTEE_4_UUID);

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch');

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }
}
