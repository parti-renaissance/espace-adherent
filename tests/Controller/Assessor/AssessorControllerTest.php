<?php

namespace Tests\App\Controller\Assessor;

use App\Entity\AssessorOfficeEnum;
use App\Mailer\Message\Assessor\AssessorRequestConfirmationMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class AssessorControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private const ASSESSOR_REQUEST_PATH = '/assesseur/';

    public function testAssessorRequest()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AssessorRequestConfirmationMessage::class));

        // Step 1
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Informations personnelles', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('1/3', $crawler->filter('span.step')->text());

        $this->client->submit($crawler->filter('form[name="assessor_request"]')->form([
            'assessor_request' => [
                'gender' => 'male',
                'firstName' => 'Ernestino',
                'lastName' => 'Bonsoirini',
                'address' => '39 rue du Welsh',
                'postalCode' => '59290',
                'city' => 'Wasquehal',
                'country' => 'FR',
                'voteCity' => 'Lille',
                'officeNumber' => '001',
                'birthCity' => 'Lille',
                'emailAddress' => 'ernestino@bonsoirini.fr',
                'birthdate' => [
                    'day' => '27',
                    'month' => '10',
                    'year' => '1985',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '0620202020',
                ],
                'voterNumber' => '00001',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 2
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Informations assesseur(e)', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('2/3', $crawler->filter('span.step')->text());

        $form = $crawler->filter('form[name="assessor_request"]')->form();
        $form->getUri();

        $this->client->request('POST', $form->getUri(), [
            'assessor_request' => [
                'assessorCountry' => 'FR',
                'assessorPostalCode' => '59000',
                'assessorCity' => 'Lille',
                'votePlaceWishes' => [0 => 15],
                'office' => AssessorOfficeEnum::HOLDER,
                'electionRounds' => [0 => 'first_round'],
                'reachable' => 1,
                'acceptValuesCharter' => 1,
                '_token' => $form['assessor_request[_token]']->getValue(),
            ],
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::ASSESSOR_REQUEST_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        // Step 3
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Récapitulatif', $crawler->filter('.title h1')->text());
        $this->assertStringContainsString('3/3', $crawler->filter('span.step')->text());

        $this->assertStringContainsString('Homme', $crawler->filter('.summary-bloc tr.gender td:last-child')->text());
        $this->assertStringContainsString('Ernestino', $crawler->filter('.summary-bloc tr.firstname td:last-child')->text());
        $this->assertStringContainsString('Bonsoirini', $crawler->filter('.summary-bloc tr.lastname td:last-child')->text());
        $this->assertStringContainsString('39 rue du Welsh', $crawler->filter('.summary-bloc tr.address td:last-child')->text());
        $this->assertStringContainsString('France', $crawler->filter('.summary-bloc tr.country td:last-child')->text());
        $this->assertStringContainsString('59290', $crawler->filter('.summary-bloc tr.postalcode td:last-child')->text());
        $this->assertStringContainsString('Wasquehal', $crawler->filter('.summary-bloc tr.city td:last-child')->text());
        $this->assertStringContainsString('001', $crawler->filter('.summary-bloc tr.office-number td:last-child')->text());
        $this->assertStringContainsString('Lille', $crawler->filter('.summary-bloc tr.birthcity td:last-child')->text());
        $this->assertStringContainsString('00001', $crawler->filter('.summary-bloc tr.voter-number td:last-child')->text());
        $this->assertStringContainsString('ernestino@bonsoirini.fr', $crawler->filter(
            '.summary-bloc tr.email td:last-child')->text()
        );
        $this->assertStringContainsString('Oui', $crawler->filter('.summary-bloc tr.reachable td:last-child')->text());
        $this->assertStringContainsString('+33 6 20 20 20 20', $crawler->filter('.summary-bloc tr.phone td:last-child')->text());
        $this->assertStringContainsString('France', $crawler->filter('.summary-bloc tr.assessor-country td:last-child')->text());
        $this->assertStringContainsString('Lille', $crawler->filter('.summary-bloc tr.assessor-city td:last-child')->text());
        $this->assertStringContainsString('59000', $crawler->filter('.summary-bloc tr.assessor-postalcode td:last-child')->text());
        $this->assertStringContainsString('Titulaire', $crawler->filter('.summary-bloc tr.assessor-office td:last-child')->text());
        $this->assertStringContainsString('Salle Polyvalente De Wazemmes', $crawler->filter(
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

    #[DataProvider('provideFormValidation')]
    public function testAssessorRequestFormValidation(array $submittedValues, array $expectedErrors): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_REQUEST_PATH);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $form = $crawler->selectButton('Continuer')->form();
        $form->disableValidation();
        $form->setValues(['assessor_request' => $submittedValues]);

        $crawler = $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        foreach ($expectedErrors as $path => $messages) {
            $errorsDiv = $crawler->filter(sprintf('#assessor_request_%s_errors', $path));

            $this->assertCount(1, $errorsDiv);

            $errors = $errorsDiv->filter('.form__error');

            $this->assertCount(\count($messages), $errors);

            foreach ($messages as $index => $message) {
                $this->assertSame($message, $errors->eq($index)->text());
            }
        }
    }

    public static function provideFormValidation(): \Generator
    {
        yield 'Invalid French phone number' => [
            ['phone' => ['country' => 'FR', 'number' => '02']],
            ['phone' => ['Cette valeur n\'est pas un numéro de téléphone valide.']],
        ];
        yield 'Invalid phone country' => [
            ['phone' => ['country' => 'AA', 'number' => '123456789']],
            ['phone' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Unknown gender value' => [
            ['gender' => 'orc'],
            ['gender' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Too young to be assessor' => [
            ['birthdate' => ['year' => (new \DateTime())->format('Y'), 'month' => '1', 'day' => '1']],
            ['birthdate' => ['Vous devez être âgé d\'au moins 18 ans pour être assesseur.']],
        ];
        yield 'Invalid birthdate year' => [
            ['birthdate' => ['year' => 'abc', 'month' => '1', 'day' => '1']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Invalid birthdate month' => [
            ['birthdate' => ['year' => '2000', 'month' => '13', 'day' => '1']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Invalid birthdate day' => [
            ['birthdate' => ['year' => '2000', 'month' => '1', 'day' => '32']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
    }
}
