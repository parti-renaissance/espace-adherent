<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Consultation;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ConsultationControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const PUBLISHED_CONSULTATION_UUID = '3efda99e-70de-4052-80e5-c77c343aa697';

    public function testNonAdherentUserCanSeeConsultationList(): void
    {
        // benjyd@aol.com is an authenticated user without the Renaissance adherent tag (ROLE_USER only):
        // the consultation list is now open to any authenticated user, no longer reserved to adherents.
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/consultations');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testNonAdherentUserCanSeeConsultationDetail(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/consultations/'.self::PUBLISHED_CONSULTATION_UUID);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testAdherentCanSeeConsultationList(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/consultations');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/consultations');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }
}
