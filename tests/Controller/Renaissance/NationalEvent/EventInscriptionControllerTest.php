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
        $this->client->followRedirect();

        $inscription = $this->getRepository(EventInscription::class)->findOneBy(['addressEmail' => 'john.doe@example.com']);
        self::assertCount(1, $inscription->getPayments());
        $payment = $inscription->getPayments()[0];
        self::assertSame(6900, $inscription->amount);
        self::assertSame(6900, $payment->amount);
        self::assertSame('bus', $payment->transport);
        self::assertSame('chambre_individuelle', $payment->accommodation);
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $inscription->paymentStatus);
        self::assertSame(PaymentStatusEnum::PENDING, $payment->status);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$payment->getUuid().'/paiement-process', $this->client);
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
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $firstInscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        /** @var EventInscription $firstInscription */
        $firstInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_1']);

        self::assertSame(1, $firstInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $firstInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $firstInscription->paymentStatus);

        $this->assertCountMails(0, NationalEventInscriptionConfirmationMessage::class);

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
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $secondInscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        /** @var EventInscription $secondInscription */
        $secondInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_2']);

        self::assertSame(1, $secondInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $secondInscription->status);
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
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $thirdInscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        /** @var EventInscription $thirdInscription */
        $thirdInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_3']);

        self::assertCount(1, $payments = $thirdInscription->getPayments());
        self::assertSame(9900, $thirdInscription->amount);
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $thirdInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $thirdInscription->paymentStatus);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus/'.$thirdInscription->getUuid());

        self::assertStringContainsString('Aucun paiement n’a encore été enregistré.', $crawler->filter('body')->text());

        /** @var Payment $payment */
        $payment = $payments[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        $thirdInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_3']);

        self::assertSame(InscriptionStatusEnum::PENDING, $thirdInscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $thirdInscription->paymentStatus);
        self::assertTrue($thirdInscription->isPaymentSuccess());

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);

        $this->em->clear();

        $duplicatedInscriptions = $this->eventInscriptionRepository->findBy(['utmSource' => ['inscription_1', 'inscription_2']]);
        self::assertCount(2, $duplicatedInscriptions);

        self::assertSame(InscriptionStatusEnum::DUPLICATE, $duplicatedInscriptions[0]->status);
        self::assertSame(InscriptionStatusEnum::DUPLICATE, $duplicatedInscriptions[1]->status);

        self::assertSame(PaymentStatusEnum::PENDING, $duplicatedInscriptions[0]->paymentStatus);
        self::assertSame(PaymentStatusEnum::PENDING, $duplicatedInscriptions[1]->paymentStatus);

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus/'.$thirdInscription->getUuid());

        self::assertStringNotContainsString('Aucun paiement n’a encore été enregistré.', $crawler->filter('body')->text());
        self::assertStringContainsString('L’essentiel du Campus se déroule sur la deuxième journée.', $crawler->filter('body')->text());
        self::assertStringContainsString('Train (Paris >< Arras) Dimanche', $crawler->filter('body')->text());
        self::assertStringContainsString('50 € - Paiement accepté', $crawler->filter('body')->text());
        self::assertStringContainsString('Chambre partagée (à deux)', $crawler->filter('body')->text());
        self::assertStringContainsString('49 € - Paiement accepté', $crawler->filter('body')->text());
    }

    public function testPreviousCampusInscriptionWithFailedPaymentMarkedAsDuplicate(): void
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
                'accommodation' => 'chambre_individuelle',
                'utmSource' => 'inscription_1',
            ],
        ]);

        /** @var EventInscription $firstInscription */
        $firstInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_1']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $firstInscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $firstInscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        /** @var EventInscription $firstInscription */
        $firstInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_1']);

        self::assertSame(1, $firstInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $firstInscription->status);
        self::assertSame(PaymentStatusEnum::PENDING, $firstInscription->paymentStatus);

        $this->assertCountMails(0, NationalEventInscriptionConfirmationMessage::class);

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
                'visitDay' => 'jour_1_et_2',
                'transport' => 'gratuit',
                'utmSource' => 'inscription_2',
            ],
        ]);

        /** @var EventInscription $secondInscription */
        $secondInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_2']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s?confirmation=1', $secondInscription->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->em->clear();

        /** @var EventInscription $secondInscription */
        $secondInscription = $this->eventInscriptionRepository->findOneBy(['utmSource' => 'inscription_2']);

        self::assertSame(0, $secondInscription->countPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $secondInscription->status);
        self::assertNull($secondInscription->paymentStatus);

        $duplicatedInscriptions = $this->eventInscriptionRepository->findBy(['utmSource' => ['inscription_1', 'inscription_2']]);
        self::assertCount(2, $duplicatedInscriptions);

        self::assertSame(InscriptionStatusEnum::DUPLICATE, $duplicatedInscriptions[0]->status);
        self::assertSame(InscriptionStatusEnum::PENDING, $duplicatedInscriptions[1]->status);

        self::assertSame(PaymentStatusEnum::PENDING, $duplicatedInscriptions[0]->paymentStatus);
        self::assertNull($duplicatedInscriptions[1]->paymentStatus);

        self::assertSame('jour_1_et_2', $duplicatedInscriptions[1]->visitDay);
        self::assertSame('gratuit', $duplicatedInscriptions[1]->transport);
    }

    public function testNewCampusInscriptionMarkedAsDuplicateAfterSuccessfulPaymentOfFirstOne(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        self::assertSame([
            'train' => 7,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

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

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $inscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        self::assertSame([
            'train' => 9,
            'chambre_partagee' => 2,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        self::assertCount(1, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);

        /** @var Payment $payment */
        $payment = $payments[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'john.doe@example.com']);

        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
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

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        self::assertSame(InscriptionStatusEnum::DUPLICATE, $inscription->status);
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s?confirmation=1', $inscription->getUuid()), $this->client);

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);
        $this->assertCountMails(1, NationalEventInscriptionDuplicateMessage::class);
    }

    public function testNewCampusInscriptionMarkedAsAcceptedAfterSuccessfulPayment(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => 'renaissance-user-2@en-marche-dev.fr',
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

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'renaissance-user-2@en-marche-dev.fr']);

        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);
        self::assertSame(9900, $inscription->amount);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $inscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        $this->client->submitForm('Continuer vers ma banque');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Continuer vers ma banque');

        $this->em->clear();

        self::assertSame([
            'train' => 9,
            'chambre_partagee' => 2,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'renaissance-user-2@en-marche-dev.fr']);

        self::assertCount(1, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);
        self::assertNotNull($inscription->adherent);

        /** @var Payment $payment */
        $payment = $payments[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => 'renaissance-user-2@en-marche-dev.fr']);

        self::assertSame(InscriptionStatusEnum::ACCEPTED, $inscription->status);
        self::assertTrue($inscription->isPaymentSuccess());

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);
    }

    public function testICanEditMyTransportChoiceFromFreeToFree(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => $email = 'test-update-free-to-free@en-marche-dev.fr',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_2',
                'transport' => 'gratuit',
                'accommodation' => 'gratuit',
            ],
        ]);

        self::assertSame([
            'train' => 7,
            'gratuit' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $this->assertCountMails(1, NationalEventInscriptionConfirmationMessage::class);

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(null, $inscription->amount);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s?confirmation=1', $inscription->getUuid()), $this->client);

        $crawler = $this->client->followRedirect();
        $section = $crawler->filter('section');

        self::assertStringContainsString('Je n\'ai pas besoin d\'hébergement', $section->text());
        self::assertStringContainsString('Je viens par mes propres moyens', $section->text());

        self::assertCount(0, $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertNull($inscription->paymentStatus);
        self::assertSame('gratuit', $inscription->transport);
        self::assertSame('gratuit', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);

        $crawler = $this->client->clickLink('Changer de forfait');

        self::assertStringContainsString('Changer de forfait', $crawler->filter('form')->text());

        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            'campus_transport' => [
                'visitDay' => 'jour_2',
                'transport' => 'gratuit',
                'accommodation' => 'gratuit',
            ],
        ]);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$inscription->getUuid(), $this->client);

        self::assertSame([
            'train' => 7,
            'gratuit' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $this->em->clear();

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        self::assertCount(0, $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertNull($inscription->paymentStatus);
        self::assertSame('gratuit', $inscription->transport);
        self::assertSame('gratuit', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);
    }

    public function testICanEditMyTransportChoiceFromSuccessfullyPayedToFree(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => $email = 'test-update-payed-to-free@en-marche-dev.fr',
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

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $inscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        self::assertSame([
            'train' => 9,
            'chambre_partagee' => 2,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $payment = $inscription->getPayments()[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertTrue($inscription->isPaymentSuccess());
        self::assertCount(1, $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(9900, $inscription->amount);
        self::assertSame('train', $inscription->transport);
        self::assertSame('chambre_partagee', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);

        $this->client->request('GET', \sprintf('/grand-rassemblement/campus/%s', $inscription->getUuid()));
        $crawler = $this->client->clickLink('Changer de forfait');

        self::assertStringContainsString('Changer de forfait', $crawler->filter('form')->text());

        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            'campus_transport' => [
                'visitDay' => 'jour_2',
                'transport' => 'gratuit',
                'accommodation' => 'gratuit',
            ],
        ]);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$inscription->getUuid(), $this->client);

        self::assertSame([
            'train' => 7,
            'gratuit' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $this->em->clear();

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        self::assertCount(1, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertNull($inscription->paymentStatus);
        self::assertNull($inscription->amount);
        self::assertSame('gratuit', $inscription->transport);
        self::assertSame('gratuit', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);
        self::assertTrue($payments[0]->isConfirmed());
        self::assertTrue($payments[0]->toRefund);
    }

    public function testICanEditMyTransportChoiceFromSuccessfullyPayedToAnotherPayedChoiceWithoutFinishPayment(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => $email = 'test-update-payed-to-free@en-marche-dev.fr',
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

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $inscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        self::assertSame([
            'train' => 9,
            'chambre_partagee' => 2,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $payment = $inscription->getPayments()[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertTrue($inscription->isPaymentSuccess());
        self::assertCount(1, $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(9900, $inscription->amount);
        self::assertSame('train', $inscription->transport);
        self::assertSame('chambre_partagee', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);

        $this->client->request('GET', \sprintf('/grand-rassemblement/campus/%s', $inscription->getUuid()));
        $crawler = $this->client->clickLink('Changer de forfait');

        self::assertStringContainsString('Changer de forfait', $crawler->filter('form')->text());

        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            'campus_transport' => [
                'visitDay' => 'jour_2',
                'transport' => 'bus',
                'accommodation' => 'chambre_individuelle',
            ],
        ]);

        self::assertSame([
            'train' => 8,
            'bus' => 1,
            'chambre_partagee' => 1,
            'chambre_individuelle' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $this->em->clear();

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$inscription->getPayments()[1]->getUuid().'/paiement-process', $this->client);

        self::assertCount(2, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(9900, $inscription->amount);
        self::assertSame('train', $inscription->transport);
        self::assertSame('chambre_partagee', $inscription->accommodation);
        self::assertFalse($inscription->withDiscount);
        self::assertTrue($payments[0]->isConfirmed());
        self::assertFalse($payments[0]->toRefund);
        self::assertTrue($payments[1]->isPending());
        self::assertFalse($payments[1]->toRefund);
    }

    public function testICanEditMyTransportChoiceFromSuccessfullyPayedToAnotherPayedChoiceAndFinishPayment(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/campus');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $buttonCrawlerNode = $crawler->selectButton('Je réserve ma place');

        $form = $buttonCrawlerNode->form();

        $form['campus_event_inscription[acceptCgu]']->tick();
        $form['campus_event_inscription[acceptMedia]']->tick();

        $this->client->submit($form, [
            'campus_event_inscription' => [
                'email' => $email = 'test-update-payed-to-free@en-marche-dev.fr',
                'civility' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'birthPlace' => 'Paris',
                'birthdate' => ['year' => '2000', 'month' => '10', 'day' => '2'],
                'postalCode' => '75001',
                'visitDay' => 'jour_2',
                'transport' => 'train',
                'accommodation' => 'chambre_partagee',
                'roommateIdentifier' => '123-456',
            ],
        ]);

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        /** @var EventInscription $inscription */
        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement', $inscription->getUuid()), $this->client);
        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo(\sprintf('/grand-rassemblement/campus/%s/paiement-process', $inscription->getPayments()[0]->getUuid()), $this->client);
        $this->client->followRedirect();

        self::assertSame([
            'train' => 9,
            'chambre_partagee' => 2,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $payment = $inscription->getPayments()[0];

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 8,
            'chambre_partagee' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertTrue($inscription->isPaymentSuccess());
        self::assertCount(1, $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(9900, $inscription->amount);
        self::assertSame('train', $inscription->transport);
        self::assertSame('chambre_partagee', $inscription->accommodation);
        self::assertSame('123-456', $inscription->roommateIdentifier);
        self::assertFalse($inscription->withDiscount);

        $this->client->request('GET', \sprintf('/grand-rassemblement/campus/%s', $inscription->getUuid()));
        $crawler = $this->client->clickLink('Changer de forfait');

        self::assertStringContainsString('Changer de forfait', $crawler->filter('form')->text());

        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            'campus_transport' => [
                'visitDay' => 'jour_2',
                'transport' => 'bus',
                'accommodation' => 'chambre_individuelle',
                'roommateIdentifier' => '123-789',
            ],
        ]);

        $this->em->clear();

        self::assertSame([
            'train' => 8,
            'bus' => 1,
            'chambre_partagee' => 1,
            'chambre_individuelle' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        $inscription = $this->eventInscriptionRepository->findOneBy(['addressEmail' => $email]);

        $payment = $inscription->getPayments()[1];

        $this->assertClientIsRedirectedTo('/grand-rassemblement/campus/'.$payment->getUuid().'/paiement-process', $this->client);

        self::assertSame('123-789', $inscription->roommateIdentifier);

        $this->bus->dispatch(new PaymentStatusUpdateCommand(['orderID' => $payment->getUuid()->toString(), 'STATUS' => '9']));

        self::assertSame([
            'train' => 7,
            'bus' => 1,
            'chambre_individuelle' => 1,
        ], $this->eventInscriptionRepository->countPlacesByTransport(3));

        self::assertCount(2, $payments = $inscription->getPayments());
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(6900, $inscription->amount);
        self::assertSame('bus', $inscription->transport);
        self::assertSame('chambre_individuelle', $inscription->accommodation);
        self::assertSame('123-789', $inscription->roommateIdentifier);
        self::assertFalse($inscription->withDiscount);
        self::assertTrue($payments[0]->isConfirmed());
        self::assertTrue($payments[0]->toRefund);
        self::assertTrue($payments[1]->isConfirmed());
        self::assertFalse($payments[1]->toRefund);
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
