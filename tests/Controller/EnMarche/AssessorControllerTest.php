<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Mailer\Message\AssessorRequestConfirmationMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AssessorControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const ASSESSOR_REQUEST_PATH = '/assesseur/demande';

    public function testAssessorRequest()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AssessorRequestConfirmationMessage::class));

        // Step 1
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Informations personnelles', $crawler->filter('.title h1')->text());
        $this->assertContains('1/3', $crawler->filter('span.step')->text());

        $this->client->submit($crawler->filter('form[name="assessor_request"]')->form([
            'assessor_request' => [
                'gender' => 'male',
                'firstName' => 'Ernestino',
                'lastName' => 'Bonsoirini',
                'birthName' => 'Boujourini',
                'address' => '39 rue du Welsh',
                'postalCode' => '59290',
                'city' => 'Wasquehal',
                'voteCity' => 'Lille',
                'officeNumber' => '001',
                'birthCity' => 'Lille',
                'emailAddress' => 'ernestino@bonsoirini.fr',
                'birthdate' => [
                    'year' => '1985',
                    'month' => '10',
                    'day' => '27',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '0620202020',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 2
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Informations assesseur(e)', $crawler->filter('.title h1')->text());
        $this->assertContains('2/3', $crawler->filter('span.step')->text());

        $form = $crawler->filter('form[name="assessor_request"]')->form();
        $form->getUri();

        $this->client->request('POST', $form->getUri(), [
            'assessor_request' => [
                'assessorCountry' => 'FR',
                'assessorPostalCode' => '59000',
                'assessorCity' => 'Lille',
                'votePlaceWishes' => [0 => 1],
                'office' => AssessorOfficeEnum::HOLDER,
                'reachable' => 1,
                'acceptDataTreatment' => 1,
                'acceptValuesCharter' => 1,
                '_token' => $form['assessor_request[_token]']->getValue(),
            ],
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 3
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Récapitulatif', $crawler->filter('.title h1')->text());
        $this->assertContains('3/3', $crawler->filter('span.step')->text());

        $this->assertContains('Homme', $crawler->filter('.summary-bloc tr.gender td:last-child')->text());
        $this->assertContains('Ernestino', $crawler->filter('.summary-bloc tr.firstname td:last-child')->text());
        $this->assertContains('Bonsoirini', $crawler->filter('.summary-bloc tr.lastname td:last-child')->text());
        $this->assertContains('39 rue du Welsh', $crawler->filter('.summary-bloc tr.address td:last-child')->text());
        $this->assertContains('59290', $crawler->filter('.summary-bloc tr.postalcode td:last-child')->text());
        $this->assertContains('Wasquehal', $crawler->filter('.summary-bloc tr.city td:last-child')->text());
        $this->assertContains('Wasquehal', $crawler->filter('.summary-bloc tr.city td:last-child')->text());
        $this->assertContains('001', $crawler->filter('.summary-bloc tr.office-number td:last-child')->text());
        $this->assertContains('Lille', $crawler->filter('.summary-bloc tr.birthcity td:last-child')->text());
        $this->assertContains('ernestino@bonsoirini.fr', $crawler->filter(
            '.summary-bloc tr.email td:last-child')->text()
        );
        $this->assertContains('Oui', $crawler->filter('.summary-bloc tr.reachable td:last-child')->text());
        $this->assertContains('+33 6 20 20 20 20', $crawler->filter('.summary-bloc tr.phone td:last-child')->text());
        $this->assertContains('France', $crawler->filter('.summary-bloc tr.assessor-country td:last-child')->text());
        $this->assertContains('Lille', $crawler->filter('.summary-bloc tr.assessor-city td:last-child')->text());
        $this->assertContains('59000', $crawler->filter('.summary-bloc tr.assessor-postalcode td:last-child')->text());
        $this->assertContains('Titulaire', $crawler->filter('.summary-bloc tr.assessor-office td:last-child')->text());
        $this->assertContains('Salle Polyvalente De Wazemmes', $crawler->filter(
            '.summary-bloc tr.assessor-vote-place-wishes td:last-child')->text()
        );

        $this->client->submit($crawler->selectButton('Valider')->form([
                'g-recaptcha-response' => 'dummy',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Confirmation
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Votre demande a bien été prise en compte.');
        $this->assertCount(1, $this->getEmailRepository()->findMessages(AssessorRequestConfirmationMessage::class));
    }

    /**
     * @dataProvider provideFormError
     * @expectedException \InvalidArgumentException
     */
    public function testAssessorRequestFormErrors(array $testedValue)
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->client->submit($crawler->filter('form[name="assessor_request"]')->form([
            'assessor_request' => [
                $testedValue,
            ],
        ]));
    }

    public function provideFormError(): \Generator
    {
        yield 'Test wrong gender' => [['gender' => 'orc']];
        yield 'Test wrong phone number' => ['phone' => ['country' => 'TO']];
        yield 'Test wrong birthdate' => ['birthdate' => [
            'year' => (new \DateTime())->format('Y'),
            'month' => '01',
            'day' => '01',
        ]];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
