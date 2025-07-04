<?php

namespace Tests\App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Mailer\Message\Renaissance\NationalEventInscriptionConfirmationMessage;
use App\Mailer\Message\Renaissance\NationalEventInscriptionDuplicateMessage;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class EventInscriptionControllerTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private ?EventInscriptionRepository $eventInscriptionRepository = null;
    private ?MessageBusInterface $bus = null;

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
                'accommodation' => 'chambre_individuelle',
            ],
        ]);

        $inscription = $this->getRepository(EventInscription::class)->findOneBy(['addressEmail' => 'john.doe@example.com']);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$inscription->getUuid().'/paiement', $this->client);
    }

    public function testPreviousCampusInscriptionMarkedAsDuplicateAfterSuccessfulPaymentOfLastOne(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => $email = 'john.doe@example.com',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_2',
                'transport' => 'train',
                'accommodation' => 'chambre_individuelle',
                'utmSource' => 'inscription_1',
            ],
        ]);

        /** @var EventInscription $firstInscription */
        $firstInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_1']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $firstInscription->getUuid()), $this->client);

        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        /** @var EventInscription $firstInscription */
        $firstInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_1']);

        self::assertSame(1, $firstInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $firstInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $firstInscription->paymentStatus);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

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
                'transport' => 'train',
                'accommodation' => 'chambre_individuelle',
                'utmSource' => 'inscription_2',
            ],
        ]);

        /** @var EventInscription $secondInscription */
        $secondInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_2']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $secondInscription->getUuid()), $this->client);

        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        /** @var EventInscription $secondInscription */
        $secondInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_2']);

        self::assertSame(1, $secondInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $secondInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $secondInscription->paymentStatus);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

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
                'transport' => 'train',
                'accommodation' => 'chambre_partagee',
                'utmSource' => 'inscription_3',
            ],
        ]);

        $this->assertCountMails(0, NationalEventInscriptionConfirmationMessage::class);

        /** @var EventInscription $thirdInscription */
        $thirdInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_3']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $thirdInscription->getUuid()), $this->client);

        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        /** @var EventInscription $thirdInscription */
        $thirdInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_3']);

        self::assertCount(1, $payments = $thirdInscription->getPayments());
        self::assertSame(9900, $thirdInscription->amount);
        self::assertSame(InscriptionStatusEnum::PENDING, $thirdInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $thirdInscription->paymentStatus);

        /** @var Payment $payment */
        $payment = $payments[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        $thirdInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_3']);

        self::assertSame(InscriptionStatusEnum::PAYMENT_CONFIRMED, $thirdInscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $thirdInscription->paymentStatus);
        self::assertTrue($thirdInscription->isPaymentSuccess());

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);

        $this->em->clear();

        $duplicatedInscriptions = $this->eventInscriptionRepository->findBy(['utmSource' => ['inscription_1', 'inscription_2']]);
        self::assertCount(2, $duplicatedInscriptions);

        self::assertSame(InscriptionStatusEnum::DUPLICATE, $duplicatedInscriptions[0]->status);
        self::assertSame(InscriptionStatusEnum::DUPLICATE, $duplicatedInscriptions[1]->status);

        self::assertNull($duplicatedInscriptions[0]->paymentStatus);
        self::assertNull($duplicatedInscriptions[1]->paymentStatus);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus/'.$thirdInscription->getUuid());

        self::assertStringContainsString('L’essentiel du Campus se déroule sur la deuxième journée.', $crawler->filter('body')->text());
        self::assertStringContainsString('Train (Paris >< Arras) Dimanche', $crawler->filter('body')->text());
        self::assertStringContainsString('50 € - Paiement accepté', $crawler->filter('body')->text());
        self::assertStringContainsString('Chambre partagée (à deux)', $crawler->filter('body')->text());
        self::assertStringContainsString('49 € - Paiement accepté', $crawler->filter('body')->text());
    }

    public function testNewCampusInscriptionMarkedAsDuplicateAfterSuccessfulPaymentOfFirstOne(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

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
                'transport' => 'train',
                'accommodation' => 'chambre_partagee',
            ],
        ]);

        $this->assertCountMails(0, NationalEventInscriptionConfirmationMessage::class);

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);

        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        self::assertCount(1, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);

        /** @var Payment $payment */
        $payment = $payments[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        self::assertSame(InscriptionStatusEnum::PAYMENT_CONFIRMED, $inscription->status);
        self::assertTrue($inscription->isPaymentSuccess());

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

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
                'transport' => 'train',
                'accommodation' => 'chambre_partagee',
                'utmSource' => 'duplicate',
            ],
        ]);

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'duplicate']);

        self::assertSame(InscriptionStatusEnum::DUPLICATE, $inscription->status);
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s?confirmation=1', $inscription->getUuid()), $this->client);

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);
        $this->assertCountMails(1, NationalEventInscriptionDuplicateMessage::class);
    }

    public static function provideReferrerCodes(): iterable
    {
        yield ['123-456', 'michelle.dufour@example.ch'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventInscriptionRepository = $this->getRepository(EventInscription::class);
        $this->em = $this->getEntityManager(EventInscription::class);
        $this->bus = $this->get(MessageBusInterface::class);

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em = null;
        $this->eventInscriptionRepository = null;
        $this->bus = null;
    }
}
