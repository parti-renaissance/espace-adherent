<?php

namespace Controller\Renaissance\Adhesion;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class PersistEmailControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    /** @dataProvider getEmails */
    public function testPersistEmailEndpoint(string $email, int $status): void
    {
        $this->client->jsonRequest('POST', '/api/persist-email', ['email' => $email, 'recaptcha' => 'fake']);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        if ($status < 2) {
            self::assertSame('OK', $response);
            self::assertNotNull($this->getRepository(AdherentRequest::class)->findOneBy(['email' => $email]));

            return;
        }

        $this->assertStatusCode(400, $this->client);
        self::assertNull($this->getRepository(AdherentRequest::class)->findOneBy(['email' => $email]));
    }

    public static function getEmails(): \Generator
    {
        yield current($params = ['techsupport@parti-renaissance.fr', 0]) => $params;
        yield current($params = ['warding-email@parti-renaissance123.fr', 1]) => $params;
        yield current($params = ['warding-email@yopmail.com', 2]) => $params;
        yield current($params = ['disabled-email@test.com', 2]) => $params;
        yield current($params = ['invalid-email', 2]) => $params;
        yield current($params = ['invalid-email@parti-renaissance', 2]) => $params;
        yield current($params = ['invalid-email@parti-renaissance..fr', 2]) => $params;
        yield current($params = ['', 2]) => $params;
    }
}
