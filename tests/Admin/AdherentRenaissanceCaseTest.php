<?php

namespace Tests\App\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\MyTeam;
use App\Entity\Unregistration;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountCreatedMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentTerminateMembershipMessage;
use App\Repository\AdherentRepository;
use App\Repository\UnregistrationRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class AdherentRenaissanceCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    private const ADHERENT_EDIT_URI_PATTERN = '/app/adherent/%d/edit';

    private ?AdherentRepository $adherentRepository = null;
    private ?UnregistrationRepository $unregistrationRepository = null;

    #[DataProvider('provideTerminateMembershipForbidden')]
    public function testTerminateMembershipForbidden(string $email): void
    {
        $this->authenticateAsAdmin($this->client);

        $adherent = $this->adherentRepository->findOneByEmail($email);
        $this->assertInstanceOf(Adherent::class, $adherent);

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringNotContainsString('Supprimer cet adhérent ⚠️', $crawler->filter('ul.dropdown-menu')->text());

        $this->client->request(Request::METHOD_GET, \sprintf('/app/adherent/%s/terminate-membership', $adherent->getId()));
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo(\sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Il est possible de faire désadhérer uniquement les adhérents sans aucun rôle (animateur, référent, candidat etc.).',
            $crawler->filter('.alert.alert-danger ')->text()
        );
    }

    public static function provideTerminateMembershipForbidden(): \Generator
    {
        yield 'PAD' => ['president-ad@renaissance-dev.fr'];
        yield 'RCL' => ['adherent-male-55@en-marche-dev.fr'];
        yield 'Délégué de circonscription' => ['deputy@en-marche-dev.fr'];
    }

    #[DataProvider('provideTerminateMembershipSuccess')]
    public function testTerminateMembershipSuccess(
        string $email,
        string $fullName,
        bool $notification,
        bool $isRenaissance,
    ): void {
        $this->authenticateAsAdmin($this->client);

        $adherent = $this->adherentRepository->findOneByEmail($email);
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertSame(0, $this->unregistrationRepository->count([]));
        $this->assertCountMails(0, RenaissanceAdherentTerminateMembershipMessage::class, $email);
        $adherentUuid = $adherent->getUuid()->toString();

        $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->clickLink('Supprimer cet adhérent ⚠️');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(
            \sprintf('/app/adherent/%s/terminate-membership', $adherent->getId()),
            $this->client->getRequest()->getPathInfo()
        );

        $this->client->submitForm('Confirmer', [
            'unregistration' => [
                'comment' => 'Unregistered.',
                'notification' => $notification,
            ],
        ]);
        $this->assertClientIsRedirectedTo('/app/adherent/list', $this->client, false, false, false);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            "L'adhérent $fullName ($email) a bien été supprimé.",
            $crawler->filter('.alert.alert-success ')->text()
        );

        $this->assertNull($this->adherentRepository->findOneByEmail($email));
        $this->assertSame(1, $this->unregistrationRepository->count([]));
        $this->assertCountMails($notification ? 1 : 0, RenaissanceAdherentTerminateMembershipMessage::class, $email);

        $unregistration = $this->unregistrationRepository->findOneBy(['uuid' => $adherentUuid]);
        $this->assertInstanceOf(Unregistration::class, $unregistration);
        $this->assertSame('Compte supprimé via action administrateur.', $unregistration->getReasons()[0]);
        $this->assertSame('Unregistered.', $unregistration->getComment());
        $this->assertSame($isRenaissance, $unregistration->isRenaissance());
    }

    public static function provideTerminateMembershipSuccess(): \Generator
    {
        // Simple user
        yield ['simple-test-user@example.ch', 'Simple User', false, false];
        yield ['simple-user@example.ch', 'Simple User', true, false];
        yield ['coalitions-user-1@en-marche-dev.fr', 'Luis Phplover', false, true];
        yield ['je-mengage-user-2@en-marche-dev.fr', 'Jerome Musk', true, false];
        // Adhérent EM
        yield ['michelle.dufour@example.ch', 'Michelle Dufour', false, false];
        yield ['bernard.morin@example.fr', 'Bernard Morin', true, false];
        // Adhérent RE
        yield ['renaissance-user-1@en-marche-dev.fr', 'Laure Fenix', true, true];
    }

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $this->assertStringNotContainsString('Exclure cet adhérent ⚠️', $crawler->filter('ul.dropdown-menu')->text());

        $this->client->request(Request::METHOD_GET, \sprintf('/app/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(403, $this->client->getResponse());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->markTestSkipped('Enable this test when the check will be ready on adhesion form');

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->clickLink('Exclure cet adhérent ⚠️');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(
            \sprintf('/app/adherent/%s/ban', $adherent->getId()),
            $this->client->getRequest()->getPathInfo()
        );

        $crawler = $this->client->submitForm('Confirmer');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(
            '/app/adherent/list',
            $this->client->getRequest()->getPathInfo()
        );

        $this->assertStringContainsString(
            \sprintf('L\'adhérent %s a bien été exclu.', $adherent->getFullName()),
            $crawler->filter('.alert.alert-success ')->text()
        );

        $this->client->getCookieJar()->clear();
        $crawler = $this->client->request('GET', '/adhesion');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submit(
            $crawler->selectButton('Étape suivante')->form(),
            [
                'frc-captcha-solution' => 'fake',
                'app_renaissance_membership' => [
                    'firstName' => 'John',
                    'lastName' => 'SMITH',
                    'address' => [
                        'country' => 'FR',
                        'address' => '62 avenue des Champs-Élysées',
                        'postalCode' => '75008',
                        'cityName' => 'Paris 8ème',
                    ],
                    'password' => 'secret!12345',
                    'emailAddress' => [
                        'first' => 'cedric.lebon@en-marche-dev.fr',
                        'second' => 'cedric.lebon@en-marche-dev.fr',
                    ],
                ],
            ]
        );

        $this->assertStringContainsString('Oups, quelque chose s\'est mal passé', $crawler->text());
    }

    public function testASuperAdminCanBanAllAdherentWithoutMainRoles(): void
    {
        $this->markTestSkipped();

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        $countError = 0;

        /** @var Adherent $adherent */
        foreach ($this->adherentRepository->findAll() as $adherent) {
            $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
            $this->assertStatusCode(Response::HTTP_OK, $this->client);

            $this->client->clickLink('Exclure cet adhérent ⚠️');

            if (!str_ends_with($this->client->getRequest()->getPathInfo(), '/ban')) {
                ++$countError;
                continue;
            }

            $this->assertSame(
                \sprintf('/app/adherent/%s/ban', $adherent->getId()),
                $this->client->getRequest()->getPathInfo()
            );

            $this->assertStatusCode(Response::HTTP_OK, $this->client);

            $crawler = $this->client->submitForm('Confirmer');

            $this->assertSame(
                '/app/adherent/list',
                $this->client->getRequest()->getPathInfo()
            );

            $this->assertStringContainsString(
                \sprintf('L\'adhérent %s a bien été exclu.', $adherent->getFullName()),
                $crawler->filter('.alert.alert-success ')->text()
            );
        }

        self::assertSame(6, $countError);
    }

    public function testCorrespondentDelegatedAccessChangedWhenAdherentLostAndRegainHisAccess()
    {
        /** @var Adherent $adherent */
        $adherent = $this->manager->getRepository(Adherent::class)->findOneByEmail('je-mengage-user-1@en-marche-dev.fr');

        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(2, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Mettre à jour')->form();

        $values = $form->getPhpValues()[$formName];
        $values['lastName'] = 'Fullstackk';
        $values['zoneBasedRoles'] = [];

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertClientIsRedirectedTo(\sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        // regain role
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

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

        $this->assertClientIsRedirectedTo(\sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $myTeams = $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(current($myTeams)->getMembers()->count(), $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));
    }

    #[DataProvider('provideCreateRenaissanceAdherent')]
    public function testCreateRenaissanceAdherent(array $submittedValues, float $expectedAmount): void
    {
        self::assertNull($this->adherentRepository->findOneByEmail($submittedValues['email']));
        self::assertNull($this->getDonatorRepository()->findOneForMatching(
            $submittedValues['email'],
            $submittedValues['firstName'],
            $submittedValues['lastName']
        ));

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/app/adherent/create-renaissance');
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/app/adherent/create-adherent-verify-email', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        self::assertStringContainsString(
            'Le paramètre email_address est manquant ou invalide',
            $crawler->filter('.alert-danger')->text()
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/app/adherent/create-renaissance?email_address='.$submittedValues['email']);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_create' => $submittedValues,
        ]));

        var_dump($this->client->getResponse()->getContent());
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/app/adherent/create-adherent-verify-email', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        self::assertStringContainsString(
            \sprintf(
                '%s %s (%s)',
                $submittedValues['firstName'],
                $submittedValues['lastName'],
                $submittedValues['email']
            ),
            $crawler->filter('.alert-success')->text()
        );

        $adherent = $this->adherentRepository->findOneByEmail($submittedValues['email']);

        self::assertInstanceOf(Adherent::class, $adherent);
        self::assertSame('PENDING', $adherent->getStatus());
        self::assertSame('renaissance', $adherent->getSource());
        self::assertSame($submittedValues['gender'], $adherent->getGender());
        self::assertSame($submittedValues['firstName'], $adherent->getFirstName());
        self::assertSame($submittedValues['lastName'], $adherent->getLastName());
        self::assertSame($submittedValues['nationality'], $adherent->getNationality());
        self::assertSame($submittedValues['address']['address'], $adherent->getAddress());
        self::assertSame($submittedValues['address']['postalCode'], $adherent->getPostalCode());
        self::assertSame($submittedValues['address']['cityName'], $adherent->getCityName());
        self::assertSame('77000-77288', $adherent->getCity());
        self::assertSame($submittedValues['address']['country'], $adherent->getCountry());
        self::assertIsNumeric($adherent->getLatitude());
        self::assertIsNumeric($adherent->getLongitude());
        self::assertSame($submittedValues['email'], $adherent->getEmailAddress());
        self::assertSame(['adherent:a_jour_'.date('Y').':primo'], $adherent->tags);
        self::assertEquals(new \DateTime('-20 years, january 1st'), $adherent->getBirthdate());
        self::assertSame('exclusive' === $submittedValues['partyMembership'], $adherent->isExclusiveMembership());
        self::assertSame('agir' === $submittedValues['partyMembership'], $adherent->isAgirMembership());
        self::assertSame('territoires_progres' === $submittedValues['partyMembership'], $adherent->isTerritoireProgresMembership());

        $registeredAt = $adherent->getRegisteredAt();
        self::assertSame($submittedValues['cotisationDate']['year'], $registeredAt->format('Y'));
        self::assertSame($submittedValues['cotisationDate']['month'], $registeredAt->format('m'));
        self::assertSame($submittedValues['cotisationDate']['day'], $registeredAt->format('d'));

        $donator = $this->getDonatorRepository()->findOneForMatching($submittedValues['email'], $submittedValues['firstName'], $submittedValues['lastName']);

        self::assertInstanceOf(Donator::class, $donator);
        self::assertSame($submittedValues['gender'], $donator->getGender());
        self::assertSame($submittedValues['firstName'], $donator->getFirstName());
        self::assertSame($submittedValues['lastName'], $donator->getLastName());

        $donation = $donator->getLastSuccessfulDonation();

        self::assertInstanceOf(Donation::class, $donation);
        self::assertEquals($expectedAmount, $donation->getAmountInEuros());
        self::assertSame('check', $donation->getType());
        self::assertSame('finished', $donation->getStatus());

        $donationDate = $donation->getDonatedAt();
        self::assertSame($submittedValues['cotisationDate']['year'], $donationDate->format('Y'));
        self::assertSame($submittedValues['cotisationDate']['month'], $donationDate->format('m'));
        self::assertSame($submittedValues['cotisationDate']['day'], $donationDate->format('d'));

        self::assertInstanceOf(\DateTime::class, $adherent->getLastMembershipDonation());
        self::assertTrue($adherent->isRenaissanceAdherent());

        $this->assertCountMails(1, RenaissanceAdherentAccountCreatedMessage::class);
        $this->assertMail(RenaissanceAdherentAccountCreatedMessage::class, $submittedValues['email'], ['template_name' => 'renaissance-adherent-account-created']);
    }

    public static function provideCreateRenaissanceAdherent(): \Generator
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
                'partyMembership' => 'exclusive',
                'cotisationAmountChoice' => 'amount_30',
                'cotisationDate' => [
                    'year' => date('Y'),
                    'month' => '11',
                    'day' => '27',
                ],
            ],
            30,
        ];
    }

    #[DataProvider('provideCreateRenaissanceAdherentValidation')]
    public function testCreateRenaissanceAdherentValidation(array $submittedValues, array $expectedErrors): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/app/adherent/create-renaissance?email_address='.$submittedValues['email']);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->disableValidation();
        $form->setValues(['adherent_create' => $submittedValues]);

        $crawler = $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        foreach ($expectedErrors as $path => $messages) {
            $errorsDiv = $crawler->filter(\sprintf('#sonata-ba-field-container-adherent_create_%s', $path));

            self::assertCount(1, $errorsDiv);

            $errors = $errorsDiv->filter('.sonata-ba-field-error-messages li');

            self::assertCount(\count($messages), $errors);

            foreach ($messages as $index => $message) {
                self::assertSame($message, trim($errors->eq($index)->text()));
            }
        }
    }

    public static function provideCreateRenaissanceAdherentValidation(): \Generator
    {
        yield 'No gender' => [
            ['gender' => '', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['gender' => ['Veuillez renseigner une civilité.']],
        ];
        yield 'Invalid gender' => [
            ['gender' => 'orc', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['gender' => ['Cette civilité n\'est pas valide.']],
        ];
        yield 'No first name' => [
            ['firstName' => null, 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['firstName' => ['Cette valeur ne doit pas être vide.']],
        ];
        yield 'Too short first name' => [
            ['firstName' => 'A', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['firstName' => ['Le prénom doit comporter au moins 2 caractères.']],
        ];
        yield 'Too long first name' => [
            ['firstName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['firstName' => ['Le prénom ne peut pas dépasser 50 caractères.']],
        ];
        yield 'No last name' => [
            ['lastName' => null, 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['lastName' => ['Cette valeur ne doit pas être vide.']],
        ];
        yield 'Too long last name' => [
            ['lastName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['lastName' => ['Le nom ne peut pas dépasser 50 caractères.']],
        ];
        yield 'No nationality' => [
            ['nationality' => '', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['nationality' => ['La nationalité est requise.']],
        ];
        yield 'Invalid nationality' => [
            ['nationality' => 'ABC', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['nationality' => ['Cette nationalité n\'est pas valide.']],
        ];
        yield 'Empty address' => [
            [
                'email' => 'renaissance-user-1@en-marche-dev.fr',
                'address' => [
                    'address' => null,
                    'city' => null,
                    'cityName' => null,
                    'postalCode' => null,
                    'country' => '',
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
                'email' => 'renaissance-user-1@en-marche-dev.fr',
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
                'email' => 'renaissance-user-1@en-marche-dev.fr',
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
                'email' => 'renaissance-user-1@en-marche-dev.fr',
            ],
            [
                'address' => ['L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.'],
            ],
        ];
        yield 'No country' => [
            [
                'address' => [
                    'address' => '3 avenue Jean Jaurès',
                    'city' => null,
                    'cityName' => 'Melun',
                    'postalCode' => '77000',
                    'country' => '',
                ],
                'email' => 'renaissance-user-1@en-marche-dev.fr',
            ],
            [
                'address' => [
                    'L\'adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
                ],
            ],
        ];
        yield 'Invalid email address' => [
            ['email' => 'abc'],
            ['email' => ['Ceci n\'est pas une adresse email valide.']],
        ];
        yield 'No phone country' => [
            ['phone' => ['country' => '', 'number' => '0612345678'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['phone' => ['Cette valeur n\'est pas un numéro de téléphone valide.']],
        ];
        yield 'Invalid phone country' => [
            ['phone' => ['country' => 'ABC', 'number' => '0612345678'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['phone' => ['Ce pays n\'est pas valide.']],
        ];
        yield 'Invalid phone number' => [
            ['phone' => ['country' => 'FR', 'number' => '02'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['phone' => ['Cette valeur n\'est pas un numéro de téléphone valide.']],
        ];
        yield 'Empty birthdate' => [
            ['birthdate' => ['year' => '', 'month' => '', 'day' => ''], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['birthdate' => ['Veuillez spécifier une date de naissance.']],
        ];
        yield 'Invalid birthdate year' => [
            ['birthdate' => ['year' => '3000', 'month' => '2', 'day' => '2'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['birthdate' => ['Veuillez entrer une date de naissance valide.']],
        ];
        yield 'Invalid birthdate month' => [
            ['birthdate' => ['year' => '2000', 'month' => '13', 'day' => '2'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['birthdate' => ['Veuillez entrer une date de naissance valide.']],
        ];
        yield 'Invalid birthdate day' => [
            ['birthdate' => ['year' => '2000', 'month' => '2', 'day' => '32'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['birthdate' => ['Veuillez entrer une date de naissance valide.']],
        ];
        yield 'Too young for adhesion' => [
            ['birthdate' => ['year' => (new \DateTime('-5 years'))->format('Y'), 'month' => '2', 'day' => '2'], 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['birthdate' => ['Veuillez entrer une date de naissance valide.']],
        ];
        yield 'No membership type' => [
            ['partyMembership' => '', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['partyMembership' => ['Veuillez spécifier au moins un type d\'adhésion.']],
        ];
        yield 'Invalid membership type' => [
            ['partyMembership' => 'invalid', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['partyMembership' => ['Ce type d\'adhésion n\'est pas valide.']],
        ];
        yield 'No cotisation amount choice' => [
            ['cotisationAmountChoice' => '', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['cotisationAmountChoice' => ['Veuillez spécifier un montant de cotisation.']],
        ];
        yield 'Invalid cotisation amount choice' => [
            ['cotisationAmountChoice' => 'invalid', 'email' => 'renaissance-user-1@en-marche-dev.fr'],
            ['cotisationAmountChoice' => ['Ce montant de cotisation est invalide.']],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->unregistrationRepository = $this->getRepository(Unregistration::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->unregistrationRepository = null;

        parent::tearDown();
    }
}
