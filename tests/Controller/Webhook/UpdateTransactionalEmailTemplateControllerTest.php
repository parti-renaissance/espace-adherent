<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Entity\Email\TransactionalEmailTemplate;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class UpdateTransactionalEmailTemplateControllerTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    public function testTemplateUpdateEndpoint(): void
    {
        $this->client->jsonRequest('POST', '/templates', [
            'identifier' => 'test-email-a',
            'subject' => 'Test will be green !',
            'content' => 'Hello, this is a test email',
            'jsonContent' => '{"blocks":[{"type":"text","data":{"text":"Hello, this is a test email"}}]}',
            'parent' => null,
        ], server: ['HTTP_AUTHORIZATION' => 'Bearer abc']);

        $this->assertResponseIsSuccessful();

        $template = $this->getRepository(TransactionalEmailTemplate::class)->findOneBy(['identifier' => 'test-email-a']);
        $this->assertNotNull($template);
        $this->assertSame('Test will be green !', $template->subject);
        $this->assertSame('Hello, this is a test email', $template->getContent());
        $this->assertSame('{"blocks":[{"type":"text","data":{"text":"Hello, this is a test email"}}]}', $template->getJsonContent());

        $this->client->jsonRequest('POST', '/templates', [
            'identifier' => 'test-email-a',
            'subject' => 'Test will be green again !',
            'content' => 'Hello, juste a test email',
            'jsonContent' => '{"blocks":[{"type":"text","data":{"text":"Hello, this is a test email"}}]}',
            'parent' => null,
        ], server: ['HTTP_AUTHORIZATION' => 'Bearer abc']);

        $this->assertResponseIsSuccessful();

        $template = $this->getRepository(TransactionalEmailTemplate::class)->findOneBy(['identifier' => 'test-email-a']);
        $this->assertNotNull($template);
        $this->assertSame('Test will be green again !', $template->subject);
        $this->assertSame('Hello, juste a test email', $template->getContent());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('production_webhook_host'));
    }
}
