<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class ValidateEmailControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('getEmails')]
    public function testValidateEmailEndpoint(string $email, int $status): void
    {
        $crawler = $this->client->request('GET', '/adhesion');
        $token = $crawler->filter('#email-validation-token')->attr('value');

        $this->client->jsonRequest('POST', '/api/validate-email', ['email' => $email, 'token' => $token]);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        if (0 === $status) {
            self::assertSame('OK', $response);

            return;
        }

        if (1 === $status) {
            self::assertSame('warning', $response['status']);
        } else {
            self::assertSame('error', $response['status']);
        }

        if ($email) {
            if (1 === $status) {
                self::assertSame("Nous ne sommes pas parvenus à vérifier l'existence de cette adresse. Vérifiez votre saisie, elle peut contenir une erreur. Si elle est correcte, ignorez cette alerte.", $response['message']);
            } else {
                self::assertSame('L\'adresse « '.$email.' » n\'est pas valide.', $response['message']);
            }
        } else {
            self::assertSame('L\'adresse email est nécessaire pour continuer.', $response['message']);
        }
    }

    public static function getEmails(): \Generator
    {
        yield current($params = ['techsupport@parti-renaissance.fr', 0]) => $params;
        yield current($params = ['warding-email@parti-renaissance123.fr', 1]) => $params;
        yield current($params = ['warding-email@yopmail.com', 2]) => $params;
        yield current($params = ['disabled-email@utilisateur@parti-renaissance.fr', 2]) => $params;
        yield current($params = ['invalid-email', 2]) => $params;
        yield current($params = ['invalid-email@parti-renaissance', 2]) => $params;
        yield current($params = ['invalid-email@parti-renaissance..fr', 2]) => $params;
        yield current($params = ['', 2]) => $params;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }
}
