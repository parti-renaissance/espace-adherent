<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadIdeaData;
use AppBundle\DataFixtures\ORM\LoadIdeaThreadCommentData;
use AppBundle\DataFixtures\ORM\LoadIdeaThreadData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\IdeasWorkshop\AuthorCategoryEnum;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Entity\TurnkeyProject;
use AppBundle\Entity\Unregistration;
use AppBundle\Mailer\Message\AdherentContactMessage;
use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeCreationConfirmationMessage;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EmailRepository;
use AppBundle\Repository\UnregistrationRepository;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Cake\Chronos\Chronos;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group adherent
 */
class AdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testMyEventsPageIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/mes-evenements');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAuthenticatedAdherentCanSeeHisUpcomingAndPastEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $crawler = $this->client->click($crawler->selectLink('Mes activités')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(6, $crawler->filter('.event-registration')->count());

        $titles = $crawler->filter('.event-registration h2 a');
        $this->assertSame('Meeting de New York City', trim($titles->first()->text()));
        $this->assertSame('Projet citoyen #3', trim($titles->eq(1)->text()));
        $this->assertSame('Réunion de réflexion parisienne', trim($titles->eq(2)->text()));
        $this->assertSame('Réunion de réflexion dammarienne', trim($titles->eq(3)->text()));
        $this->assertSame('Projet citoyen Paris-18', trim($titles->eq(4)->text()));
        $this->assertSame('Réunion de réflexion parisienne annulé', trim($titles->last()->text()));

        $crawler = $this->client->click($crawler->selectLink('Événements passés')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(7, $crawler->filter('.event-registration')->count());

        $titles = $crawler->filter('.event-registration h2 a');
        $this->assertSame('Meeting de Singapour', trim($titles->first()->text()));
        $this->assertSame('Grand débat parisien', trim($titles->eq(1)->text()));
        $this->assertSame('Événement à Paris 1', trim($titles->eq(2)->text()));
        $this->assertSame('Événement à Paris 2', trim($titles->eq(3)->text()));
        $this->assertSame('Marche Parisienne', trim($titles->eq(4)->text()));
        $this->assertSame('Grand Meeting de Paris', trim($titles->eq(5)->text()));
        $this->assertSame('Grand Meeting de Marseille', trim($titles->last()->text()));
    }

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsSecured(string $profilePage): void
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function provideProfilePage()
    {
        yield 'Mon compte' => ['/parametres/mon-compte'];
        yield 'Mes informations personnelles' => ['/parametres/mon-compte/modifier'];
        yield 'Mes dons' => ['/parametres/mon-compte/mes-dons'];
        yield 'Mes centres d\'intérêt' => ['/espace-adherent/mon-compte/centres-d-interet'];
        yield 'Modifier mon profil' => ['/espace-adherent/mon-profil'];
        yield 'Notifications' => ['/parametres/mon-compte/preferences-des-emails'];
        yield 'Mot de passe' => ['/parametres/mon-compte/changer-mot-de-passe'];
    }

    public function testProfileActionIsAccessibleForAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Nom Carl Mirabeau', $crawler->filter('.settings__username')->text());
        $this->assertSame('Adhérent depuis novembre 2016.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsAccessibleForInactiveAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'thomas.leclerc@example.ch');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Nom Thomas Leclerc', $crawler->filter('.settings__username')->text());
        $this->assertSame('Non adhérent.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsNotAccessibleForDisabledAdherent(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'michelle.dufour@example.ch',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertContains('Pour vous connecter vous devez confirmer votre adhésion. Si vous n\'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau.', $crawler->filter('#auth-error')->text());
    }

    public function testEditAdherentProfile(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $oldLatitude = $adherent->getLatitude();
        $oldLongitude = $adherent->getLongitude();
        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories73Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '73');
        $histories73Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '73');

        $this->assertCount(7, $histories73Subscriptions);
        $this->assertCount(0, $histories73Unsubscriptions);
        $this->assertCount(0, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/modifier');

        $inputPattern = 'input[name="adherent[%s]"]';
        $optionPattern = 'select[name="adherent[%s]"] option[selected="selected"]';

        self::assertSame('male', $crawler->filter(sprintf($optionPattern, 'gender'))->attr('value'));
        self::assertSame('Carl', $crawler->filter(sprintf($inputPattern, 'firstName'))->attr('value'));
        self::assertSame('Mirabeau', $crawler->filter(sprintf($inputPattern, 'lastName'))->attr('value'));
        self::assertSame('122 rue de Mouxy', $crawler->filter(sprintf($inputPattern, 'address][address'))->attr('value'));
        self::assertSame('73100', $crawler->filter(sprintf($inputPattern, 'address][postalCode'))->attr('value'));
        self::assertSame('73100-73182', $crawler->filter(sprintf($inputPattern, 'address][city'))->attr('value'));
        self::assertSame('France', $crawler->filter(sprintf($optionPattern, 'address][country'))->text());
        self::assertSame('01 11 22 33 44', $crawler->filter(sprintf($inputPattern, 'phone][number'))->attr('value'));
        self::assertSame('Retraité', $crawler->filter(sprintf($optionPattern, 'position'))->text());
        self::assertSame('8', $crawler->filter(sprintf($optionPattern, 'birthdate][day'))->attr('value'));
        self::assertSame('7', $crawler->filter(sprintf($optionPattern, 'birthdate][month'))->attr('value'));
        self::assertSame('1950', $crawler->filter(sprintf($optionPattern, 'birthdate][year'))->attr('value'));
        self::assertCount(2, $adherent->getReferentTags());
        self::assertAdherentHasReferentTag($adherent, '73');
        self::assertAdherentHasReferentTag($adherent, 'CIRCO_73004');

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('adherent[submit]')->form([
            'adherent' => [
                'gender' => 'male',
                'firstName' => '',
                'lastName' => '',
                'nationality' => '',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '',
                    'city' => '10102-45029',
                    'cityName' => '',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'position' => 'student',
            ],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertSame(5, $errors->count());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Veuillez renseigner un code postal.', $errors->eq(2)->text());
        self::assertSame('L\'adresse est obligatoire.', $errors->eq(3)->text());

        // Submit the profile form with too long input
        $crawler = $this->client->submit($crawler->selectButton('adherent[submit]')->form([
            'adherent' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'nationality' => 'FR',
                'address' => [
                    'address' => 'Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Oui oui oui.',
                    'country' => 'FR',
                    'postalCode' => '0600000000000000',
                    'city' => '06000-6088',
                    'cityName' => 'Nice, France',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => [
                    'year' => '1985',
                    'month' => '10',
                    'day' => '27',
                ],
            ],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertSame(3, $errors->count());
        self::assertSame('Le code postal doit contenir moins de 15 caractères.', $errors->eq(0)->text());
        self::assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(1)->text());
        self::assertSame('L\'adresse ne peut pas dépasser 150 caractères.', $errors->eq(2)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('adherent[submit]')->form([
            'adherent' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'address' => [
                    'address' => '9 rue du Lycée',
                    'country' => 'FR',
                    'postalCode' => '06000',
                    'city' => '06000-6088',
                    'cityName' => 'Nice, France',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => [
                    'year' => '1985',
                    'month' => '10',
                    'day' => '27',
                ],
            ],
        ]));

        $this->assertClientIsRedirectedTo('/parametres/mon-compte', $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Vos informations ont été mises à jour avec succès.');

        // We need to reload the manager reference to get the updated data
        /** @var Adherent $adherent */
        $adherent = $this->client->getContainer()->get('doctrine')->getManager()->getRepository(Adherent::class)->findOneByEmail('carl999@example.fr');

        self::assertSame('female', $adherent->getGender());
        self::assertSame('Jean Dupont', $adherent->getFullName());
        self::assertSame('9 rue du Lycée', $adherent->getAddress());
        self::assertSame('06000', $adherent->getPostalCode());
        self::assertSame('Nice', $adherent->getCityName());
        self::assertSame('401020304', $adherent->getPhone()->getNationalNumber());
        self::assertSame('student', $adherent->getPosition());
        $this->assertNotNull($newLatitude = $adherent->getLatitude());
        $this->assertNotNull($newLongitude = $adherent->getLongitude());
        $this->assertNotSame($oldLatitude, $newLatitude);
        $this->assertNotSame($oldLongitude, $newLongitude);
        self::assertCount(2, $adherent->getReferentTags());
        self::assertAdherentHasReferentTag($adherent, '06');
        self::assertAdherentHasReferentTag($adherent, 'CIRCO_06001');

        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories73Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '73');
        $histories73Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '73');

        $this->assertCount(7, $histories73Subscriptions);
        $this->assertCount(7, $histories73Unsubscriptions);
        $this->assertCount(7, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);
    }

    public function testEditMandates(): void
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $adherent = $this->getAdherentRepository()->findOneByEmail('michel.vasseur@example.ch');
        $ideas = $this->getIdeaRepository()->findBy(['author' => $adherent]);

        $this->checkIdeasAuthorCategory($ideas, AuthorCategoryEnum::ELECTED);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/modifier');

        $form = $crawler->selectButton('adherent[submit]')->form();
        $form['adherent[elected]']->untick();

        // Submit the profile form with valid data
        $this->client->submit($form, [
            'adherent' => [
                'gender' => 'male',
                'firstName' => 'Michel',
                'lastName' => 'VASSEUR',
                'address' => [
                    'address' => '12 Pilgerweg',
                    'country' => 'CH',
                    'postalCode' => '8802',
                    'cityName' => 'Kilchberg',
                ],
                'nationality' => 'CH',
                'phone' => [
                    'country' => 'CH',
                    'number' => '31 359 21 11',
                ],
                'position' => 'employed',
                'birthdate' => [
                    'year' => '1987',
                    'month' => '10',
                    'day' => '13',
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte', $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Vos informations ont été mises à jour avec succès.');

        // We need to reload the manager reference to get the updated data
        $this->manager->clear();
        /** @var Adherent $adherent */
        $adherent = $this->client->getContainer()->get('doctrine')->getManager()->getRepository(Adherent::class)->findOneByEmail('michel.vasseur@example.ch');
        $ideas = $this->getIdeaRepository()->findBy(['author' => $adherent]);

        self::assertSame([], $adherent->getMandates());
        $this->checkIdeasAuthorCategory($ideas, AuthorCategoryEnum::ADHERENT);
    }

    public function testEditAdherentInterests(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/centres-d-interet');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = '#app_adherent_pin_interests '.
                           'input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        $interests = $this->client->getContainer()->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);
        $interestsLabels = array_values($interests);

        foreach ($checkboxes as $i => $checkbox) {
            self::assertSame($interestsValues[$i], $checkbox->getAttribute('value'));
            self::assertSame($interestsLabels[$i], $crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->text());
        }

        $interests = $this->client->getContainer()->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);

        $chosenInterests = [
            4 => $interestsValues[4],
            8 => $interestsValues[8],
        ];

        $this->client->submit($crawler->selectButton('app_adherent_pin_interests[submit]')->form(), [
            'app_adherent_pin_interests' => [
                'interests' => $chosenInterests,
            ],
        ]);

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/centres-d-interet', $this->client);

        /* @var Adherent $adherent */
        $this->container->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');

        self::assertSame(array_values($chosenInterests), $adherent->getInterests());

        $crawler = $this->client->followRedirect();

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        foreach ($checkboxes as $i => $checkbox) {
            if (isset($chosenInterests[$i])) {
                self::assertSame('checked', $checkbox->getAttribute('checked'));
            } else {
                $this->assertEmpty($crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->attr('checked'));
            }
        }
    }

    public function testAdherentChangePassword(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/changer-mot-de-passe');

        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[old_password]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][first]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][second]"]'));

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => '',
                'password' => [
                    'first' => '',
                    'second' => '',
                ],
            ],
        ]);

        $errors = $crawler->filter('.form__errors > li');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(2, $errors->count());
        self::assertSame('Le mot de passe est invalide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => 'secret!12345',
                'password' => [
                    'first' => 'heaneaheah',
                    'second' => 'heaneaheah',
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/changer-mot-de-passe', $this->client);
    }

    public function testAdherentSetEmailNotifications(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');

        $this->assertFalse($adherent->hasSubscribedLocalHostEmails());
        $this->assertNotEmpty($adherent->getSubscriptionTypeCodes());

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/preferences-des-emails');
        $subscriptions = $crawler->filter('input[name="adherent_email_subscription[subscriptionTypes][]"]');

        $this->assertCount(9, $subscriptions);

        // Submit the emails subscription form with invalid data
        // We need to use a POST request because the crawler does not
        // accept any invalid choice, thus cannot submit invalid form
        $crawler = $this->client->request(Request::METHOD_POST, '/parametres/mon-compte/preferences-des-emails', [
            'adherent_email_subscription' => [
                'subscriptionTypes' => ['heah'],
                '_token' => $crawler->filter('input[name="adherent_email_subscription[_token]"]')->attr('value'),
            ],
        ]);

        $errors = $crawler->filter('.form__errors > li');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(1, $errors->count());
        self::assertSame('Cette valeur n\'est pas valide.', $errors->eq(0)->text());

        // Submit the emails subscription form with valid data
        Chronos::setTestNow('+1 day');
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);
        $historiesCitizenProjectHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL);

        $this->assertCount(11, $histories);
        $this->assertCount(1, $historiesHost);
        $this->assertCount(1, $historiesReferents);
        $this->assertCount(2, $historiesCitizenProjectHost);
        self::assertSame('subscribe', $historiesHost[0]->getAction());
        self::assertSame('subscribe', $historiesReferents[0]->getAction());
        self::assertSame('unsubscribe', $historiesCitizenProjectHost[0]->getAction());
        self::assertSame('subscribe', $historiesCitizenProjectHost[1]->getAction());
        $this->assertTrue($adherent->hasSubscribedLocalHostEmails());
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::REFERENT_EMAIL));
        $this->assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::DEPUTY_EMAIL));

        // Unsubscribe from 'subscribed_emails_local_host' and 'subscribed_emails_referents'
        Chronos::setTestNow('+1 week');
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');
        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);

        $this->assertCount(14, $histories);
        $this->assertCount(2, $historiesHost);
        $this->assertCount(2, $historiesReferents);
        self::assertSame('unsubscribe', $historiesHost[0]->getAction());
        self::assertSame('unsubscribe', $historiesReferents[0]->getAction());

        Chronos::setTestNow('+2 weeks'); // To make sure the date order of the SQL query is correct
        // Re-subscribe to 'subscribed_emails_local_host' and 'subscribed_emails_referents'
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'subscriptionTypes' => $this->getSubscriptionTypesFormValues([
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                    SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                    SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    SubscriptionTypeEnum::DEPUTY_EMAIL,
                ]),
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();

        $histories = $this->findEmailSubscriptionHistoryByAdherent($adherent);
        $historiesHost = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
        $historiesReferents = $this->findAllEmailSubscriptionHistoryByAdherentAndType($adherent, SubscriptionTypeEnum::REFERENT_EMAIL);

        $this->assertCount(17, $histories);
        $this->assertCount(3, $historiesHost);
        $this->assertCount(3, $historiesReferents);
        self::assertSame('subscribe', $historiesHost[0]->getAction());
        self::assertSame('subscribe', $historiesReferents[0]->getAction());
        Chronos::setTestNow();
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findEmailSubscriptionHistoryByAdherent(
        Adherent $adherent,
        string $action = null,
        string $referentTagCode = null
    ): array {
        $qb = $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->where('history.adherentUuid = :adherentUuid')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->orderBy('history.date', 'DESC')
        ;

        if ($action) {
            $qb
                ->andWhere('history.action = :action')
                ->setParameter('action', $action)
            ;
        }

        if ($referentTagCode) {
            $qb
                ->leftJoin('history.referentTags', 'tag')
                ->andWhere('tag.code = :code')
                ->setParameter('code', $referentTagCode)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findAllEmailSubscriptionHistoryByAdherentAndType(
        Adherent $adherent,
        string $subscriptionType
    ): array {
        return $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->join('history.subscriptionType', 'subscriptionType')
            ->where('history.adherentUuid = :adherentUuid')
            ->andWhere('subscriptionType.code = :type')
            ->orderBy('history.date ', 'DESC')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->setParameter('type', $subscriptionType)
            ->getQuery()
            ->getResult()
        ;
    }

    public function testAnonymousUserCannotCreateCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider provideAdherentsAllowedToCreateCitizenProject
     */
    public function testAdherentThatCanCreateNewCitizenProject(string $emailAddress): void
    {
        $this->authenticateAsAdherent($this->client, $emailAddress);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    /**
     * @dataProvider provideAdherentsNotAllowedToCreateCitizenProject
     */
    public function testAdherentThatCanNotCreateNewCitizenProject(string $emailAddress): void
    {
        $this->authenticateAsAdherent($this->client, $emailAddress);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCreateCitizenProjectFailed(): void
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $data = [];
        $this->client->submit($this->client->getCrawler()->selectButton('Proposer mon projet')->form(), $data);

        $errors = $this->client->getCrawler()->filter('.form__errors');

        $this->assertSame(7, $errors->count());

        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-name > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être nulle.',
            $this->client->getCrawler()->filter('#field-category > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-subtitle > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-problem-description > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-proposed-solution > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-required-means > .form__errors > li')->text()
        );
        $this->assertSame(
            'Le numéro de téléphone est obligatoire.',
            $this->client->getCrawler()->filter('#citizen-project-phone > .form__errors > li')->text()
        );

        $data = [];
        $data['citizen_project']['name'] = 'P';
        $data['citizen_project']['subtitle'] = 'test';
        $this->client->submit($this->client->getCrawler()->selectButton('Proposer mon projet')->form(), $data);

        $this->assertSame(7, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Vous devez saisir au moins 2 caractères.',
            $this->client->getCrawler()->filter('#field-name > .form__errors > li')->text()
        );
        $this->assertSame(
            'Vous devez saisir au moins 5 caractères.',
            $this->client->getCrawler()->filter('#field-subtitle > .form__errors > li')->text()
        );
    }

    public function testCreateCitizenProjectSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $categoryValue = $crawler->filter('#citizen_project_category option:contains("Culture")')->attr('value');
        $this->assertSame(0, $crawler->filter('#citizen_project_district')->count());

        $data = [];
        $data['citizen_project']['name'] = 'mon Projet Citoyen';
        $data['citizen_project']['subtitle'] = 'mon premier projet citoyen';
        $data['citizen_project']['category'] = $categoryValue;
        $data['citizen_project']['problem_description'] = 'Le problème local.';
        $data['citizen_project']['proposed_solution'] = 'Ma solution.';
        $data['citizen_project']['required_means'] = 'Mes actions.';
        $data['citizen_project']['address']['postalCode'] = '8802';
        $data['citizen_project']['address']['cityName'] = 'Kilchberg';
        $data['citizen_project']['address']['country'] = 'CH';
        $data['citizen_project']['phone']['country'] = 'CH';
        $data['citizen_project']['phone']['number'] = '31 359 21 11';

        $this->client->submit($this->client->getCrawler()->selectButton('Proposer mon projet')->form(), $data);

        $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
        $this->assertTrue($this->seeDefaultCitizenProjectImage());

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneBy(['name' => 'Mon Projet Citoyen']);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertInstanceOf(CitizenProject::class, $citizenProject);
        $this->assertSame('Mon Projet Citoyen', $citizenProject->getName());
        $this->assertSame('Mon premier projet citoyen', $citizenProject->getSubtitle());
        $this->assertCountMails(1, CitizenProjectCreationConfirmationMessage::class, 'carl999@example.fr');
    }

    public function testAdherentCanCreateNewCitizenProjectEventTurnkeyProjectDoesNotExist(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen/wrong-turn-key');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testCreateTwoCitizenProjectFromTheSameTurnkeyProjectSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $turnkeyProject = $this->getTurnkeyProjectRepository()->findOneBy(['slug' => 'art-s-connection']);
        $this->assertInstanceOf(TurnkeyProject::class, $turnkeyProject);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen/art-s-connection');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame('Art\'s connection', $crawler->filter('#citizen_project_name_text')->text());
        $this->assertSame('Ateliers de rencontre autour de l\'art', $crawler->filter('#citizen_project_subtitle_text')->text());
        $this->assertSame('Culture', $crawler->filter('#citizen_project_category_text')->text());
        $this->assertSame('Les lieux et espaces de culture sont rarement accessibles à tous et donnent peu l\'occasion de tisser du lien social.', $crawler->filter('#citizen_project_problem_description_text')->text());
        $this->assertSame('Nous proposons d\'organiser des ateliers d\'art participatif associant des artistes aux citoyens', $crawler->filter('#citizen_project_proposed_solution_text')->text());
        $this->assertSame(1, $crawler->filter('#citizen_project_district')->count());

        $form = $this->client->getCrawler()->selectButton('Proposer mon projet')->form();
        $form->setValues([
            'citizen_project[required_means]' => 'Mes actions.',
            'citizen_project[phone][number]' => '6 55 66 77 66',
            'citizen_project[phone][country]' => 'FR',
        ]);

        $this->client->submit($form);

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneBy(['slug' => '73100-arts-connection']);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertInstanceOf(CitizenProject::class, $citizenProject);
        $this->assertSame('Art\'s connection', $citizenProject->getName());
        $this->assertSame('Ateliers de rencontre autour de l\'art', $citizenProject->getSubtitle());
        $this->assertSame('Culture', $citizenProject->getCategory()->getName());
        $this->assertSame('Les lieux et espaces de culture sont rarement accessibles à tous et donnent peu l\'occasion de tisser du lien social.', $citizenProject->getProblemDescription());
        $this->assertSame('Nous proposons d\'organiser des ateliers d\'art participatif associant des artistes aux citoyens', $citizenProject->getProposedSolution());
        $this->assertSame('Mes actions.', $citizenProject->getRequiredMeans());
        $this->assertSame('Mouxy', $citizenProject->getDistrict());
        $this->assertSame($turnkeyProject, $citizenProject->getTurnkeyProject());

        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen/art-s-connection');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame('Art\'s connection', $crawler->filter('#citizen_project_name_text')->text());
        $this->assertSame('Ateliers de rencontre autour de l\'art', $crawler->filter('#citizen_project_subtitle_text')->text());
        $this->assertSame('Culture', $crawler->filter('#citizen_project_category_text')->text());
        $this->assertSame('Les lieux et espaces de culture sont rarement accessibles à tous et donnent peu l\'occasion de tisser du lien social.', $crawler->filter('#citizen_project_problem_description_text')->text());
        $this->assertSame('Nous proposons d\'organiser des ateliers d\'art participatif associant des artistes aux citoyens', $crawler->filter('#citizen_project_proposed_solution_text')->text());

        $form = $crawler->selectButton('Proposer mon projet')->form();
        $form->setValues([
            'citizen_project[required_means]' => 'Mes actions aussi.',
            'citizen_project[phone][number]' => '6 22 33 44 55',
            'citizen_project[phone][country]' => 'FR',
            'citizen_project[district]' => 'Mon quartier',
        ]);

        $this->client->submit($form);

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneBy(['slug' => '77000-arts-connection']);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertInstanceOf(CitizenProject::class, $citizenProject);
        $this->assertSame('Art\'s connection', $citizenProject->getName());
        $this->assertSame('Ateliers de rencontre autour de l\'art', $citizenProject->getSubtitle());
        $this->assertSame('Culture', $citizenProject->getCategory()->getName());
        $this->assertSame('Les lieux et espaces de culture sont rarement accessibles à tous et donnent peu l\'occasion de tisser du lien social.', $citizenProject->getProblemDescription());
        $this->assertSame('Nous proposons d\'organiser des ateliers d\'art participatif associant des artistes aux citoyens', $citizenProject->getProposedSolution());
        $this->assertSame('Mes actions aussi.', $citizenProject->getRequiredMeans());
        $this->assertSame('Mon quartier', $citizenProject->getDistrict());
        $this->assertSame($turnkeyProject, $citizenProject->getTurnkeyProject());
        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(CitizenProjectCreationConfirmationMessage::class, 'referent@en-marche-dev.fr'));
    }

    /**
     * @dataProvider provideCommitteesHostsAdherentsCredentials
     */
    public function testCommitteesAdherentsHostsAreNotAllowedToCreateNewCommittees(string $emailAddress): void
    {
        $this->authenticateAsAdherent($this->client, $emailAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        // Try to cheat the system with a direct URL access.
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideCommitteesHostsAdherentsCredentials(): array
    {
        return [
            'Jacques Picard is already the owner of an existing committee' => [
                'jacques.picard@en-marche.fr',
            ],
            'Gisèle Berthoux was promoted the host privilege of an existing committee' => [
                'gisele-berthoux@caramail.com',
            ],
        ];
    }

    /**
     * @dataProvider provideRegularAdherentsCredentials
     */
    public function testRegularAdherentCanCreateOneNewCommittee(string $emaiLAddress, string $phone): void
    {
        $this->client->followRedirects();

        $this->authenticateAsAdherent($this->client, $emaiLAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $crawler = $this->client->click($crawler->selectLink('Créer un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame($phone, $crawler->filter('#create_committee_phone_number')->attr('value'));

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'create_committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'facebookPageUrl' => 'yo',
                'twitterNickname' => '@!!',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(10, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame('Cette valeur n\'est pas un code postal français valide.', $crawler->filter('#committee-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#field-address > .form__errors > li')->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('.form__tel .form__errors > li')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $crawler = $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'create_committee[name]' => 'lyon est En Marche !',
            'create_committee[description]' => 'Comité français En Marche ! de la ville de Lyon',
            'create_committee[address][country]' => 'FR',
            'create_committee[address][address]' => '6 rue Neyret',
            'create_committee[address][postalCode]' => '69001',
            'create_committee[address][city]' => '69001-69381',
            'create_committee[address][cityName]' => '',
            'create_committee[phone][country]' => 'FR',
            'create_committee[phone][number]' => '0478457898',
            'create_committee[facebookPageUrl]' => 'https://www.facebook.com/EnMarcheLyon',
            'create_committee[twitterNickname]' => '@enmarchelyon',
            'create_committee[acceptConfidentialityTerms]' => true,
            'create_committee[acceptContactingTerms]' => true,
            'create_committee[photo]' => new UploadedFile(__DIR__.'/../../Fixtures/image.jpg', 'image.jpg', 'image/jpeg', 631, \UPLOAD_ERR_OK, true),
        ]));

        $this->assertInstanceOf(Committee::class, $committee = $this->committeeRepository->findMostRecentCommittee());
        $this->assertSame('Lyon est En Marche !', $committee->getName());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeCreationConfirmationMessage::class, $emaiLAddress));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Votre comité a été créé avec succès. Il est en attente de validation par nos équipes.');
        $this->assertSame('Lyon est En Marche !', $crawler->filter('#committee_name')->attr('value'));
        $this->assertSame('Comité français En Marche ! de la ville de Lyon', $crawler->filter('#committee_description')->text());

        $this->assertSame('04 78 45 78 98', $crawler->filter('#committee_phone_number')->attr('value'));
        $this->assertSame(1, $crawler->filter('#field-photo img')->count());
    }

    public function provideRegularAdherentsCredentials(): array
    {
        return [
            ['carl999@example.fr', '01 11 22 33 44'],
            ['luciole1989@spambox.fr', '07 27 36 36 43'],
        ];
    }

    public function testDocumentsActionSecured(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testDocumentsActionIsAccessibleAsAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Documents', $this->client->getResponse()->getContent());
    }

    public function testContactActionSecured(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testContactActionForAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Contacter Michelle Dufour', $this->client->getResponse()->getContent());

        $this->client->submit($crawler->selectButton('Envoyer')->form([
            'g-recaptcha-response' => 'dummy',
            'contact_message' => [
                'content' => 'A message I would like to send to Miss Dufour',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Votre message a bien été envoyé.');

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(AdherentContactMessage::class));
    }

    public function testContactActionWithInvalidUuid(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/wrong-uuid');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'event',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'committee',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    /**
     * @dataProvider dataProviderCannotTerminateMembership
     */
    public function testCannotTerminateMembership(string $email): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(0, $crawler->filter('.settings_unsubscribe'));

        $this->client->request(Request::METHOD_GET, '/mon-compte/desadherer');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function dataProviderCannotTerminateMembership()
    {
        yield 'Host' => ['gisele-berthoux@caramail.com'];
        yield 'Referent' => ['referent@en-marche-dev.fr'];
        yield 'BoardMember' => ['carl999@example.fr'];
    }

    /**
     * @dataProvider provideAdherentCredentials
     */
    public function testAdherentTerminatesMembership(
        string $userEmail,
        string $uuid,
        int $nbComments,
        string $committee,
        int $nbFollowers
    ): void {
        /** @var Adherent $adherent */
        $adherentBeforeUnregistration = $this->getAdherentRepository()->findOneByEmail($userEmail);
        $referentTagsBeforeUnregistration = $adherentBeforeUnregistration->getReferentTags()->toArray(); // It triggers the real SQL query instead of lazy-load

        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $crawler->filter('.settings__delete_account'));

        $crawler = $this->client->click($crawler->selectLink('Supprimer définitivement ce compte')->link());
        $this->assertEquals('http://'.$this->hosts['app'].'/parametres/mon-compte/desadherer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form([
            'unregistration' => [],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(1, $errors->count());
        $this->assertSame('Afin de confirmer la suppression de votre compte, veuillez sélectionner la raison pour laquelle vous quittez le mouvement.', $errors->eq(0)->text());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', $committee));
        $this->assertSame("$nbFollowers adhérents", $crawler->filter('.committee-members')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/desadherer');
        $reasons = Unregistration::REASONS_LIST_ADHERENT;
        $reasonsValues = array_values($reasons);
        $chosenReasons = [
            1 => $reasonsValues[1],
            3 => $reasonsValues[3],
        ];

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form([
            'unregistration' => [
                'reasons' => $chosenReasons,
                'comment' => 'Je me désinscris',
            ],
        ]));

        $this->assertEquals('http://'.$this->hosts['app'].'/parametres/mon-compte/desadherer', $this->client->getRequest()->getUri());

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(0, $errors->count());
        $this->assertSame('Votre adhésion et votre compte En Marche ont bien été supprimés et vos données personnelles effacées de notre base.', trim($crawler->filter('#is_not_adherent h1')->eq(0)->text()));

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(AdherentTerminateMembershipMessage::class, $userEmail));

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', 'en-marche-suisse'));
        --$nbFollowers;

        $this->assertSame("$nbFollowers adhérents", $crawler->filter('.committee-members')->text());

        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findOneByEmail($userEmail);

        $this->assertNull($adherent);

        /** @var Unregistration $unregistration */
        $unregistration = $this->get(UnregistrationRepository::class)->findOneByUuid($uuid);
        $mailHistorySubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'subscribe');
        $mailHistoryUnsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'unsubscribe');

        $this->assertSame(\count($mailHistorySubscriptions), \count($mailHistoryUnsubscriptions));
        $this->assertSame(array_values($chosenReasons), $unregistration->getReasons());
        $this->assertSame('Je me désinscris', $unregistration->getComment());
        $this->assertSame($adherentBeforeUnregistration->getRegisteredAt()->format('Y-m-d H:i:s'), $unregistration->getRegisteredAt()->format('Y-m-d H:i:s'));
        $this->assertSame((new \DateTime())->format('Y-m-d'), $unregistration->getUnregisteredAt()->format('Y-m-d'));
        $this->assertSame($adherentBeforeUnregistration->getUuid()->toString(), $unregistration->getUuid()->toString());
        $this->assertSame($adherentBeforeUnregistration->getPostalCode(), $unregistration->getPostalCode());
        $this->assertEquals($referentTagsBeforeUnregistration, $unregistration->getReferentTags()->toArray());

        if (LoadAdherentData::ADHERENT_13_UUID === $uuid) {
            $draftIdea = $this->getRepository(Idea::class)->findOneBy(['uuid' => LoadIdeaData::IDEA_05_UUID]);
            $pendingIdea = $this->getRepository(Idea::class)->findOneBy(['uuid' => LoadIdeaData::IDEA_06_UUID]);
            $finalizedIdea = $this->getRepository(Idea::class)->findOneBy(['uuid' => LoadIdeaData::IDEA_07_UUID]);
            $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => LoadIdeaThreadData::THREAD_07_UUID]);
            $threadComment = $this->getRepository(ThreadComment::class)->findOneBy(['uuid' => LoadIdeaThreadCommentData::THREAD_COMMENT_08_UUID]);

            $this->assertNull($draftIdea);
            $this->assertNull($pendingIdea);
            $this->assertNotNull($finalizedIdea);
            $this->assertNull($finalizedIdea->getAuthor());
            $this->assertNull($thread);
            $this->assertNull($threadComment);
        }
    }

    public function provideAdherentCredentials(): array
    {
        return [
            'adherent 1' => ['michel.vasseur@example.ch', LoadAdherentData::ADHERENT_13_UUID, 2, 'en-marche-suisse', 3],
            'adherent 2' => ['luciole1989@spambox.fr', LoadAdherentData::ADHERENT_4_UUID, 1, 'en-marche-paris-8', 4],
        ];
    }

    public function provideAdherentsAllowedToCreateCitizenProject(): array
    {
        return [
            'Adherent without citizen project' => [
                'carl999@example.fr',
            ],
            'Citizen project admin with an approved and a refused committee' => [
                'jacques.picard@en-marche.fr',
            ],
            'Citizen project admin with 3 approved committees' => [
                'francis.brioul@yahoo.com',
            ],
        ];
    }

    public function provideAdherentsNotAllowedToCreateCitizenProject(): array
    {
        return [
            'Adherent with a pending citizen project' => [
                'benjyd@aol.com',
            ],
            'Adherent with a pre-approved citizen project' => [
                'kiroule.p@blabla.tld',
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->committeeRepository = null;

        parent::tearDown();
    }

    private function getSubscriptionTypesFormValues(array $codes)
    {
        return array_map(static function (SubscriptionType $type) use ($codes) {
            return \in_array($type->getCode(), $codes, true) ? $type->getId() : false;
        }, $this->getSubscriptionTypeRepository()->findByCodes(SubscriptionTypeEnum::ADHERENT_TYPES));
    }

    private function checkIdeasAuthorCategory(array $ideas, string $categoryName)
    {
        foreach ($ideas as $idea) {
            $this->assertSame($categoryName, $idea->getAuthorCategory());
        }
    }
}
