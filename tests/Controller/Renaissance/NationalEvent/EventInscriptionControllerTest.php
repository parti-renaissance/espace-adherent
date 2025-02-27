<?php

namespace Tests\App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Repository\NationalEvent\EventInscriptionRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class EventInscriptionControllerTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private ?EventInscriptionRepository $eventInscriptionRepository = null;

    #[DataProvider('provideReferrerCodes')]
    public function testEventInscriptionWithReferral(string $referrerCode, ?string $referrerEmail): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, "/grand-rassemblement/event-national-1/$referrerCode");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['event_inscription[acceptCgu]']->tick();
        $form['event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
            ],
        ]);
        $this->assertClientIsRedirectedTo('/grand-rassemblement/event-national-1/confirmation', $this->client);

        $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $eventInscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);
        $this->assertInstanceOf(EventInscription::class, $eventInscription);
        $this->assertEquals($referrerCode, $eventInscription->referrerCode);
        $this->assertEquals($referrerEmail, $eventInscription->referrer?->getEmailAddress());
    }

    public static function provideReferrerCodes(): iterable
    {
        yield ['123-456', 'michelle.dufour@example.ch'];
        yield ['invalid', null];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventInscriptionRepository = $this->getRepository(EventInscription::class);

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->eventInscriptionRepository = null;
    }
}
