<?php

namespace Tests\App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\InscriptionStatusEnum;
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

        $form['default_event_inscription[acceptCgu]']->tick();
        $form['default_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'default_event_inscription' => [
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

    public function testAutoAcceptedStatus(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/event-national-1');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['default_event_inscription[acceptCgu]']->tick();
        $form['default_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'default_event_inscription' => [
                'email' => $email = 'gisele-berthoux@caramail.com',
                'civility' => 'female',
                'firstName' => 'Gisele',
                'lastName' => 'Berthoux',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
            ],
        ]);
        $this->assertClientIsRedirectedTo('/grand-rassemblement/event-national-1/confirmation', $this->client);

        $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $eventInscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);
        $this->assertInstanceOf(EventInscription::class, $eventInscription);
        self::assertSame(InscriptionStatusEnum::ACCEPTED, $eventInscription->status);
    }

    public function testAutoDuplicateStatus(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/event-national-1');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['default_event_inscription[acceptCgu]']->tick();
        $form['default_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'default_event_inscription' => [
                'email' => $email = 'john.doe@example.com',
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

        $eventInscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);
        $this->assertInstanceOf(EventInscription::class, $eventInscription);
        self::assertSame(InscriptionStatusEnum::PENDING, $eventInscription->status);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/event-national-1');

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['default_event_inscription[acceptCgu]']->tick();
        $form['default_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'default_event_inscription' => [
                'email' => $email,
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

        $eventInscriptions = $this->eventInscriptionRepository->findBy(['addressEmail' => $email]);
        $this->assertCount(2, $eventInscriptions);

        self::assertSame(InscriptionStatusEnum::PENDING, $eventInscriptions[0]->status);
        self::assertSame(InscriptionStatusEnum::DUPLICATE, $eventInscriptions[1]->status);
    }

    public function testCampusInscription(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $crawler = $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
            ],
        ]);

        $this->assertStringContainsString('Veillez sélectionner votre jour de visite.', $crawler->filter('body')->text());

        $crawler = $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_1_et_2',
            ],
        ]);

        $this->assertStringContainsString('Veillez sélectionner le forfait.', $crawler->filter('body')->text());

        $crawler = $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_1_et_2',
                'transport' => 'bus',
            ],
        ]);

        $this->assertStringContainsString('Le mode de transport sélectionné n\'est pas disponible pour le jour de visite choisi.', $crawler->filter('body')->text());

        $em = $this->getEntityManager();
        $event = $em->getRepository(NationalEvent::class)->findOneBy(['slug' => 'campus']);
        $event->transportConfiguration['transports'][0]['quota'] = 0;
        $em->flush();

        $crawler = $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_2',
                'transport' => 'train',
            ],
        ]);

        $this->assertStringContainsString('Le quota de places pour ce mode de transport est atteint.', $crawler->filter('body')->text());

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_2',
                'transport' => 'bus',
            ],
        ]);

        $inscription = $this->getRepository(EventInscription::class)->findOneBy(['addressEmail' => 'john.doe@example.com']);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$inscription->getUuid().'/paiement', $this->client);
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
