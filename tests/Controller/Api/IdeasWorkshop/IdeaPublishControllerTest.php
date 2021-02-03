<?php

namespace Tests\App\Controller\Api\IdeasWorkshop;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class IdeaPublishControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testPublishDraftIdeaGeneratesBadResponse(): void
    {
        $this->disableRepublicanSilence();

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request('PUT', '/api/ideas-workshop/ideas/c14937d6-fd42-465c-8419-ced37f3e6194/publish');

        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('You can publish only draft idea', $response->getContent());
    }

    protected function setUp(): void
    {
        $this->init();
        parent::setUp();
    }
}
