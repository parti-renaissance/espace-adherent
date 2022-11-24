<?php

namespace Tests\App\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Donator;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\MyTeam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdherentAdminTest extends AbstractWebCaseTest
{
    use ControllerTestTrait;

    private const ADHERENT_EDIT_URI_PATTERN = '/admin/app/adherent/%d/edit';

    private $adherentRepository;

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(403, $this->client->getResponse());
    }

    public function testEditBoardMemberInformations()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
        $this->authenticateAsAdmin($this->client);
        $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId());
        // Empty roles should revoke board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->untick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only area should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only roles should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill area and roles should grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));
        $this->assertEquals('Exclure cet adhérent ⚠️', trim($navBar->getNode(3)->nodeValue));
        $this->assertEquals('Certifier cet adhérent', trim($navBar->getNode(4)->nodeValue));

        $link = $crawler->selectLink('Exclure cet adhérent ⚠️')->link();
        $crawler = $this->client->click($link);

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertStringContainsString(sprintf('L\'adhérent <b>%s</b> a bien été exclu', $adherent->getFullName()), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/adhesion');

        $crawler = $this->client->submit(
            $crawler->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'A',
                    'nationality' => 'FR',
                    'emailAddress' => [
                        'first' => 'cedric.lebon@en-marche-dev.fr',
                        'second' => 'cedric.lebon@en-marche-dev.fr',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '1 rue des alouettes',
                        'postalCode' => '94320',
                        'cityName' => 'Thiais',
                        'city' => '94320-94073',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );

        $this->assertStringContainsString('Oups, quelque chose s\'est mal passé', $crawler->filter('#adherent_registration_emailAddress_first_errors')->text());
    }

    public function testAnAdminWithoutRoleCannotUpdateCustomInstanceQuality()
    {
        $this->authenticateAsAdmin($this->client);

        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        self::assertFalse($form->has($formName.'[instanceQualities]'));
    }

    public function testAnSuperAdminCanUpdateCustomInstanceQuality()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());
        $this->client->request('GET', '/conseil-national');
        $this->assertStatusCode(403, $this->client);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(sprintf('%s?uniqid=', $editUrl), '', $form->getFormNode()->getAttribute('action'));

        $form[$formName.'[instanceQualities]'] = 9;
        $this->client->submit($form);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $this->client->request('GET', '/conseil-national');
        $this->assertStatusCode(200, $this->client);
    }

    public function testCorrespondentDelegatedAccessChangedWhenAdherentLostAndRegainHisAccess()
    {
        /** @var Adherent $adherent */
        $adherent = $this->manager->getRepository(Adherent::class)->findOneByEmail('je-mengage-user-1@en-marche-dev.fr');

        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(2, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Mettre à jour')->form();

        $values = $form->getPhpValues()[$formName];
        $values['lastName'] = 'Fullstackk';
        $values['zoneBasedRoles'] = [];

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertClientIsRedirectedTo(sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        // regain role
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Mettre à jour')->form();

        $values = $form->getPhpValues()[$formName];
        $values['lastName'] = 'Fullstackk';
        $values['zoneBasedRoles'] = [[
            'type' => 'correspondent',
            'zones' => [291],
        ]];

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertClientIsRedirectedTo(sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $myTeams = $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(current($myTeams)->getMembers()->count(), $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));
    }

    /**
     * @dataProvider provideCreateRenaissanceAdherent
     */
    public function testCreateRenaissanceAdherent(array $submittedValues): void
    {
        self::assertNull($this->adherentRepository->findOneByEmail($submittedValues['email']));
        self::assertNull($this->getDonatorRepository()->findOneForMatching(
            $submittedValues['email'],
            $submittedValues['firstName'],
            $submittedValues['lastName'])
        );

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/app/adherent/create-renaissance');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_create' => $submittedValues,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/admin/app/adherent/create-renaissance', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        self::assertStringContainsString(
            'Le compte adhérent Renaissance a bien été créé.',
            $crawler->filter('.alert-success')->text()
        );

        $adherent = $this->adherentRepository->findOneByEmail($submittedValues['email']);

        self::assertInstanceOf(Adherent::class, $adherent);
        self::assertSame('DISABLED', $adherent->getStatus());
        self::assertSame($submittedValues['gender'], $adherent->getGender());
        self::assertSame($submittedValues['firstName'], $adherent->getFirstName());
        self::assertSame($submittedValues['lastName'], $adherent->getLastName());
        self::assertSame($submittedValues['nationality'], $adherent->getNationality());
        self::assertSame($submittedValues['address']['address'], $adherent->getAddress());
        self::assertSame($submittedValues['address']['postalCode'], $adherent->getPostalCode());
        self::assertSame($submittedValues['address']['cityName'], $adherent->getCityName());
        self::assertSame('77000-77288', $adherent->getCity());
        self::assertSame($submittedValues['address']['country'], $adherent->getCountry());
        self::assertSame(null, $adherent->getLatitude());
        self::assertSame(null, $adherent->getLongitude());
        self::assertSame($submittedValues['email'], $adherent->getEmailAddress());
        self::assertEquals(new \DateTime('-20 years, january 1st'), $adherent->getBirthdate());

        $donator = $this->getDonatorRepository()->findOneForMatching($submittedValues['email'], $submittedValues['firstName'], $submittedValues['lastName']);

        self::assertInstanceOf(Donator::class, $donator);
        self::assertSame($submittedValues['gender'], $donator->getGender());
        self::assertSame($submittedValues['firstName'], $donator->getFirstName());
        self::assertSame($submittedValues['lastName'], $donator->getLastName());
    }

    public function provideCreateRenaissanceAdherent(): \Generator
    {
        yield [
            [
                'gender' => 'male',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'nationality' => 'FR',
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => '77000',
                    'country' => 'FR',
                ],
                'email' => 'new-re-user@en-marche-dev.code',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0123456789',
                ],
                'birthdate' => [
                    'year' => (new \DateTime('-20 years'))->format('Y'),
                    'month' => 1,
                    'day' => 1,
                ],
                'membershipType' => 'exclusive',
                'cotisationAmountChoice' => 'amount_30',
            ],
        ];
    }

    /**
     * @dataProvider provideCreateRenaissanceAdherentValidation
     */
    public function testCreateRenaissanceAdherentValidation(array $submittedValues, array $expectedErrors): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/app/adherent/create-renaissance');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->disableValidation();
        $form->setValues(['adherent_create' => $submittedValues]);

        $crawler = $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        foreach ($expectedErrors as $path => $messages) {
            $errorsDiv = $crawler->filter(sprintf('#sonata-ba-field-container-adherent_create_%s', $path));

            self::assertCount(1, $errorsDiv);

            $errors = $errorsDiv->filter('.sonata-ba-field-error-messages li');

            self::assertCount(\count($messages), $errors);

            foreach ($messages as $index => $message) {
                self::assertSame($message, trim($errors->eq($index)->text()));
            }
        }
    }

    public function provideCreateRenaissanceAdherentValidation(): \Generator
    {
        yield 'No gender' => [
            ['gender' => null],
            ['gender' => ['Veuillez renseigner un genre.']],
        ];
        yield 'Invalid gender' => [
            ['gender' => 'orc'],
            ['gender' => ['Ce sexe n\'est pas valide.']],
        ];
        yield 'No first name' => [
            ['firstName' => null],
            ['firstName' => ['Cette valeur ne doit pas être vide.']],
        ];
        yield 'Too short first name' => [
            ['firstName' => 'A'],
            ['firstName' => ['Le prénom doit comporter au moins 2 caractères.']],
        ];
        yield 'Too long first name' => [
            ['firstName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit'],
            ['firstName' => ['Le prénom ne peut pas dépasser 50 caractères.']],
        ];
        yield 'No last name' => [
            ['lastName' => null],
            ['lastName' => ['Cette valeur ne doit pas être vide.']],
        ];
        yield 'Too long last name' => [
            ['lastName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit'],
            ['lastName' => ['Le nom ne peut pas dépasser 50 caractères.']],
        ];
        yield 'No nationality' => [
            ['nationality' => null],
            ['nationality' => ['La nationalité est requise.']],
        ];
        yield 'Invalid nationality' => [
            ['nationality' => 'ABC'],
            ['nationality' => ['Cette nationalité n\'est pas valide.']],
        ];
        yield 'Empty address' => [
            [
                'address' => [
                    'address' => null,
                    'city' => null,
                    'cityName' => null,
                    'postalCode' => null,
                    'country' => null,
                ],
            ],
            ['address' => ['L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.']],
        ];
        yield 'No address' => [
            [
                'address' => [
                    'address' => null,
                    'city' => null,
                    'cityName' => 'Nice',
                    'postalCode' => '06000',
                    'country' => 'FR',
                ],
            ],
            ['address' => ['L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.']],
        ];
        yield 'No postal code' => [
            [
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => null,
                    'country' => 'FR',
                ],
            ],
            ['address' => ['L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.']],
        ];
        yield 'Invalid postal code' => [
            [
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => '77abc',
                    'country' => 'FR',
                ],
            ],
            [
                'address' => [
                    'L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
                    'Cette valeur n\'est pas un code postal français valide.',
                ],
            ],
        ];
        yield 'No country' => [
            [
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => '77000',
                    'country' => null,
                ],
            ],
            [
                'address' => [
                    'L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
                ],
            ],
        ];
        yield 'Invalid country' => [
            [
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => '77000',
                    'country' => 'ABC',
                ],
            ],
            [
                'address' => [
                    'L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
                    'Ce pays n\'est pas valide.',
                ],
            ],
        ];
        yield 'No email address' => [
            ['email' => null],
            ['email' => ['Veuillez renseigner une adresse e-mail.']],
        ];
        yield 'Invalid email address' => [
            ['email' => 'abc'],
            ['email' => ['Ceci n\'est pas une adresse e-mail valide.']],
        ];
        yield 'Too long email address' => [
            ['email' => 'loremipsumdolorsitametconsecteturadipiscingelitloremipsumdolorsitametconsecteturadipiscingelitloremipsumdolorsitametconsecteturadipiscingelit@loremipsumdolorsitametconsecteturadipiscingelitloremipsumdolorsitametconsecteturadipiscingelitloremipsumdolorsitametconsecteturadipiscingelit.dev'],
            ['email' => ['L\'adresse e-mail est trop longue, 255 caractères maximum.']],
        ];
        yield 'No phone country' => [
            ['phone' => ['country' => null, 'number' => '0612345678']],
            ['phone' => ['Cette valeur n\'est pas un numéro de téléphone valide.']],
        ];
        yield 'Invalid phone country' => [
            ['phone' => ['country' => 'ABC', 'number' => '0612345678']],
            ['phone' => ['Ce pays n\'est pas valide.']],
        ];
        yield 'Invalid phone number' => [
            ['phone' => ['country' => 'FR', 'number' => '02']],
            ['phone' => ['Cette valeur n\'est pas un numéro de téléphone valide.']],
        ];
        yield 'Empty birthdate' => [
            ['birthdate' => ['year' => null, 'month' => null, 'day' => null]],
            ['birthdate' => ['Veuillez spécifier une date de naissance.']],
        ];
        yield 'Invalid birthdate year' => [
            ['birthdate' => ['year' => '3000', 'month' => '2', 'day' => '2']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Invalid birthdate month' => [
            ['birthdate' => ['year' => '2000', 'month' => '13', 'day' => '2']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Invalid birthdate day' => [
            ['birthdate' => ['year' => '2000', 'month' => '2', 'day' => '32']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'Too young for adhesion' => [
            ['birthdate' => ['year' => (new \DateTime('-5 years'))->format('Y'), 'month' => '2', 'day' => '2']],
            ['birthdate' => ['Cette valeur n\'est pas valide.']],
        ];
        yield 'No membership type' => [
            ['membershipType' => ''],
            ['membershipType' => ['Veuillez spécifier au moins un type d\'adhésion.']],
        ];
        yield 'Invalid membership type' => [
            ['membershipType' => 'invalid'],
            ['membershipType' => ['Ce type d\'adhésion n\'est pas valide.']],
        ];
        yield 'No cotisation amount choice' => [
            ['cotisationAmountChoice' => null],
            ['cotisationAmountChoice' => ['Veuillez spécifier un montant de cotisation.']],
        ];
        yield 'Invalid cotisation amount choice' => [
            ['cotisationAmountChoice' => 'invalid'],
            ['cotisationAmountChoice' => ['Ce montant de cotisation est invalide.']],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
