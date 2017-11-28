<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Unregistration;
use AppBundle\Mailer\Message\AdherentContactMessage;
use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;
use AppBundle\Mailer\Message\CommitteeCreationConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group adherent
 */
class AdherentControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testMyEventsPageIsProtected()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/mes-evenements');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $crawler = $this->client->followRedirect();

        $this->assertSame('Identifiez-vous', $crawler->filter('.login h2')->text());
    }

    public function testAuthenticatedAdherentCanSeeHisUpcomingAndPastEvents()
    {
        $crawler = $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Mes événements')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(4, $crawler->filter('.event-registration')->count());

        $titles = $crawler->filter('.event-registration h2 a');
        $this->assertSame('Meeting de New York City', trim($titles->first()->text()));
        $this->assertSame('Réunion de réflexion parisienne', trim($titles->eq(1)->text()));
        $this->assertSame('Réunion de réflexion dammarienne', trim($titles->eq(2)->text()));
        $this->assertSame('Réunion de réflexion parisienne annulé', trim($titles->last()->text()));

        $crawler = $this->client->click($crawler->selectLink('Événements passés')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(5, $crawler->filter('.event-registration')->count());

        $titles = $crawler->filter('.event-registration h2 a');
        $this->assertSame('Meeting de Singapour', trim($titles->first()->text()));
        $this->assertSame('Grand débat parisien', trim($titles->eq(1)->text()));
        $this->assertSame('Marche Parisienne', trim($titles->eq(2)->text()));
        $this->assertSame('Grand Meeting de Paris', trim($titles->eq(3)->text()));
        $this->assertSame('Grand Meeting de Marseille', trim($titles->last()->text()));
    }

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsSecured($profilePage)
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);
    }

    public function provideProfilePage()
    {
        yield ['/espace-adherent/mon-compte', 'Mon compte'];
        yield ['/espace-adherent/mon-compte/modifier', 'Mes informations personnelles'];
        yield ['/espace-adherent/mon-compte/changer-mot-de-passe', 'Mot de passe'];
        yield ['/espace-adherent/mon-compte/preferences-des-emails', 'Notifications'];
    }

    public function testProfileActionIsAccessibleForAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Carl Mirabeau', $crawler->filter('.settings__username')->text());
        $this->assertSame('Adhérent depuis novembre 2016.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsAccessibleForInactiveAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'thomas.leclerc@example.ch', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.settings .settings-menu ul li.active a'));
        $this->assertSame('Thomas Leclerc', $crawler->filter('.settings__username')->text());
        $this->assertSame('Non adhérent.', $crawler->filter('.settings__membership')->text());
        $this->assertSame('Mon compte', $crawler->filter('.settings h2')->text());
    }

    public function testProfileActionIsNotAccessibleForDisabledAdherent()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => 'michelle.dufour@example.ch',
            '_adherent_password' => 'secret!12345',
        ]));

        $this->assertClientIsRedirectedTo('http://'.$this->hosts['app'].'/espace-adherent/connexion', $this->client);

        $this->client->followRedirect();

        $this->assertContains('Oups! Vos identifiants sont invalides.', $this->client->getResponse()->getContent());
    }

    public function testEditAdherentProfile()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');
        $oldLatitude = $adherent->getLatitude();
        $oldLongitude = $adherent->getLongitude();

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/modifier');

        $inputPattern = 'input[name="update_membership_request[%s]"]';
        $optionPattern = 'select[name="update_membership_request[%s]"] option[selected="selected"]';

        $this->assertSame('male', $crawler->filter(sprintf($inputPattern, 'gender').'[checked="checked"]')->attr('value'));
        $this->assertSame('Carl', $crawler->filter(sprintf($inputPattern, 'firstName'))->attr('value'));
        $this->assertSame('Mirabeau', $crawler->filter(sprintf($inputPattern, 'lastName'))->attr('value'));
        $this->assertSame('122 rue de Mouxy', $crawler->filter(sprintf($inputPattern, 'address][address'))->attr('value'));
        $this->assertSame('73100', $crawler->filter(sprintf($inputPattern, 'address][postalCode'))->attr('value'));
        $this->assertSame('73100-73182', $crawler->filter(sprintf($inputPattern, 'address][city'))->attr('value'));
        $this->assertSame('France', $crawler->filter(sprintf($optionPattern, 'address][country'))->text());
        $this->assertSame('01 11 22 33 44', $crawler->filter(sprintf($inputPattern, 'phone][number'))->attr('value'));
        $this->assertSame('Retraité', $crawler->filter(sprintf($optionPattern, 'position'))->text());
        $this->assertSame('8', $crawler->filter(sprintf($optionPattern, 'birthdate][day'))->attr('value'));
        $this->assertSame('7', $crawler->filter(sprintf($optionPattern, 'birthdate][month'))->attr('value'));
        $this->assertSame('1950', $crawler->filter(sprintf($optionPattern, 'birthdate][year'))->attr('value'));
        $this->assertSame('carl999@example.fr', $crawler->filter(sprintf($inputPattern, 'emailAddress'))->attr('value'));

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('update_membership_request[submit]')->form([
            'update_membership_request' => [
                'gender' => 'male',
                'firstName' => '',
                'lastName' => '',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'position' => 'student',
                'emailAddress' => '',
            ],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(6, $errors->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        $this->assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(2)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $errors->eq(3)->text());
        $this->assertSame("L'adresse est obligatoire.", $errors->eq(4)->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(5)->text());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        // Submit the profile form with duplicate email
        $crawler = $this->client->submit($crawler->selectButton('update_membership_request[submit]')->form([
            'update_membership_request' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'address' => [
                    'address' => '9 rue du Lycée',
                    'country' => 'FR',
                    'postalCode' => '06000',
                    'city' => '06000-6088',
                    'cityName' => '',
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
                'emailAddress' => 'michelle.dufour@example.ch',
            ],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(1, $errors->count());
        $this->assertSame('Cette adresse e-mail existe déjà.', $errors->eq(0)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('update_membership_request[submit]')->form([
            'update_membership_request' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'address' => [
                    'address' => '9 rue du Lycée',
                    'country' => 'FR',
                    'postalCode' => '06000',
                    'city' => '06000-6088', // Nice
                    'cityName' => '',
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
                'emailAddress' => 'new.email@address.com',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertSame('Vos informations ont été mises à jour avec succès.', trim($crawler->filter('#notice-flashes')->text()));

        // We need to reload the manager reference to get the updated data
        /** @var Adherent $adherent */
        $adherent = $this->client->getContainer()->get('doctrine')->getManager()->getRepository(Adherent::class)->findByEmail('new.email@address.com');

        $this->assertSame('female', $adherent->getGender());
        $this->assertSame('Jean Dupont', $adherent->getFullName());
        $this->assertSame('9 rue du Lycée', $adherent->getAddress());
        $this->assertSame('06000', $adherent->getPostalCode());
        $this->assertSame('Nice', $adherent->getCityName());
        $this->assertSame('401020304', $adherent->getPhone()->getNationalNumber());
        $this->assertSame('student', $adherent->getPosition());
        $this->assertSame('new.email@address.com', $adherent->getEmailAddress());
        $this->assertNotNull($newLatitude = $adherent->getLatitude());
        $this->assertNotNull($newLongitude = $adherent->getLongitude());
        $this->assertNotSame($oldLatitude, $newLatitude);
        $this->assertNotSame($oldLongitude, $newLongitude);
    }

    public function testEditAdherentInterests()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/centres-d-interet');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = '#app_adherent_pin_interests '.
                           'input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        $interests = $this->client->getContainer()->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);
        $interestsLabels = array_values($interests);

        foreach ($checkboxes as $i => $checkbox) {
            $this->assertSame($interestsValues[$i], $checkbox->getAttribute('value'));
            $this->assertSame($interestsLabels[$i], $crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->text());
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
        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');

        $this->assertSame(array_values($chosenInterests), $adherent->getInterests());

        $crawler = $this->client->followRedirect();

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

        foreach ($checkboxes as $i => $checkbox) {
            if (isset($chosenInterests[$i])) {
                $this->assertSame('checked', $checkbox->getAttribute('checked'));
            } else {
                $this->assertEmpty($crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->attr('checked'));
            }
        }
    }

    public function testAdherentChangePassword()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/changer-mot-de-passe');

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
        $this->assertSame(2, $errors->count());
        $this->assertSame('Le mot de passe est invalide.', $errors->eq(0)->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());

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

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/changer-mot-de-passe', $this->client);
    }

    public function testAdherentSetEmailNotifications()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');

        $this->assertFalse($adherent->hasSubscribedLocalHostEmails());
        $this->assertTrue($adherent->hasSubscribedReferentsEmails());
        $this->assertTrue($adherent->hasSubscribedMainEmails());
        $this->assertTrue($adherent->hasCitizenProjectCreationEmailSubscription());

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/preferences-des-emails');
        $subscriptions = $crawler->filter('input[name="adherent_email_subscription[emails_subscriptions][]"]');

        $this->assertCount(4, $subscriptions);

        // Submit the emails subscription form with invalid data
        // We need to use a POST request because the crawler does not
        // accept any invalid choice, thus cannot submit invalid form
        $crawler = $this->client->request(Request::METHOD_POST, '/espace-adherent/mon-compte/preferences-des-emails', [
            'adherent_email_subscription' => [
                'emails_subscriptions' => ['heah'],
                '_token' => $crawler->filter('input[name="adherent_email_subscription[_token]"]')->attr('value'),
            ],
        ]);

        $errors = $crawler->filter('.form__errors > li');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $errors->count());
        $this->assertSame('Cette valeur n\'est pas valide.', $errors->eq(0)->text());

        // Submit the emails subscription form with valid data
        $this->client->submit($crawler->selectButton('adherent_email_subscription[submit]')->form(), [
            'adherent_email_subscription' => [
                'emails_subscriptions' => [
                    AdherentEmailSubscription::SUBSCRIBED_EMAILS_MAIN,
                    AdherentEmailSubscription::SUBSCRIBED_EMAILS_REFERENTS,
                    false,
                    false,
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/preferences-des-emails', $this->client);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');

        $this->assertFalse($adherent->hasSubscribedLocalHostEmails());
        $this->assertTrue($adherent->hasSubscribedReferentsEmails());
        $this->assertTrue($adherent->hasSubscribedMainEmails());
        $this->assertFalse($adherent->hasCitizenProjectCreationEmailSubscription());
    }

    public function testAnonymousUserCannotCreateCitizenProject()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://enmarche.dev/espace-adherent/connexion', $this->client);
    }

    public function testAdherentCanCreateNewCitizenProject()
    {
        $crawler = $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $this->assertSame(2, $crawler->selectLink('Créer un projet citoyen')->count());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testCitizenProjectAdministratorCanCreateAnotherCitizenProject()
    {
        $crawler = $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $this->assertSame(2, $crawler->selectLink('Créer un projet citoyen')->count());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testCreateCitizenProjectFailed()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $data = [];
        $this->client->submit($this->client->getCrawler()->selectButton('Créer un projet citoyen')->form(), $data);

        $this->assertSame(2, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-name > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#field-description > .form__errors > li')->text()
        );

        $data = [];
        $data['citizen_project']['name'] = 'P';
        $data['citizen_project']['description'] = 'test';
        $this->client->submit($this->client->getCrawler()->selectButton('Créer un projet citoyen')->form(), $data);

        $this->assertSame(2, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Vous devez saisir au moins 2 caractères.',
            $this->client->getCrawler()->filter('#field-name > .form__errors > li')->text()
        );
        $this->assertSame(
            'Votre texte de description est trop court. Il doit compter 5 caractères minimum.',
            $this->client->getCrawler()->filter('#field-description > .form__errors > li')->text()
        );
    }

    public function testCreateCitizenProjectSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $data = [];
        $data['citizen_project']['name'] = 'Mon projet citoyen';
        $data['citizen_project']['description'] = 'Mon premier projet citoyen';
        $data['citizen_project']['address']['address'] = 'Pilgerweg 58';
        $data['citizen_project']['address']['cityName'] = 'Kilchberg';
        $data['citizen_project']['address']['postalCode'] = '8802';
        $data['citizen_project']['address']['country'] = 'CH';
        $data['citizen_project']['phone']['country'] = 'CH';
        $data['citizen_project']['phone']['number'] = '31 359 21 11';

        $this->client->submit($this->client->getCrawler()->selectButton('Créer un projet citoyen')->form(), $data);
        $citizenProject = $this->getCitizenProjectRepository()->findOneBy(['name' => 'Mon projet citoyen']);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertInstanceOf(CitizenProject::class, $citizenProject);

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(CitizenProjectCreationConfirmationMessage::class, 'michel.vasseur@example.ch'));
    }

    public function testCreateCitizenProjectWithoutAddressAndPhoneSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-projet-citoyen');

        $data = [];
        $data['citizen_project']['name'] = 'Mon équipe';
        $data['citizen_project']['description'] = 'Ma première équipe';

        $this->client->submit($this->client->getCrawler()->selectButton('Créer un projet citoyen')->form(), $data);
        $citizenProject = $this->getCitizenProjectRepository()->findOneBy(['name' => 'Mon équipe']);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertInstanceOf(CitizenProject::class, $citizenProject);

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(CitizenProjectCreationConfirmationMessage::class, 'michel.vasseur@example.ch'));
    }

    /**
     * @dataProvider provideCommitteesHostsAdherentsCredentials
     */
    public function testCommitteesAdherentsHostsAreNotAllowedToCreateNewCommittees(string $emailAddress, string $password)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emailAddress, $password);
        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        // Try to cheat the system with a direct URL access.
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideCommitteesHostsAdherentsCredentials()
    {
        return [
            'Jacques Picard is already the owner of an existing committee' => [
                'jacques.picard@en-marche.fr',
                'changeme1337',
            ],
            'Gisèle Berthoux was promoted the host privilege of an existing committee' => [
                'gisele-berthoux@caramail.com',
                'ILoveYouManu',
            ],
        ];
    }

    /**
     * @dataProvider provideRegularAdherentsCredentials
     */
    public function testRegularAdherentCanCreateOneNewCommittee(string $emaiLAddress, string $password, string $phone)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emaiLAddress, $password);
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
                'googlePlusPageUrl' => 'yo',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(11, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame('Cette valeur n\'est pas un code postal français valide.', $crawler->filter('#committee-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#field-address > .form__errors > li')->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('.register__form__phone > .form__errors > li')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-googleplus-page-url > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'create_committee' => [
                'name' => 'Lyon est En Marche !',
                'description' => 'Comité français En Marche ! de la ville de Lyon',
                'address' => [
                    'country' => 'FR',
                    'address' => '6 rue Neyret',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                    'cityName' => '',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '0478457898',
                ],
                'facebookPageUrl' => 'https://www.facebook.com/EnMarcheLyon',
                'twitterNickname' => '@enmarchelyon',
                'googlePlusPageUrl' => 'https://plus.google.com/+EnMarcheavecEmmanuelMacron?hl=fr',
                'acceptConfidentialityTerms' => true,
                'acceptContactingTerms' => true,
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(Committee::class, $committee = $this->committeeRepository->findMostRecentCommittee());
        $this->assertSame('Lyon est En Marche !', $committee->getName());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeCreationConfirmationMessage::class, $emaiLAddress));

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Votre comité a été créé avec succès. Il est néanmoins en attente de validation par un administrateur', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Lyon est En Marche !', $crawler->filter('#committee-name')->text());
        $this->assertSame('Comité français En Marche ! de la ville de Lyon', $crawler->filter('#committee-description')->text());

        $crawler = $this->client->click($crawler->selectLink('Éditer le comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('04 78 45 78 98', $crawler->filter('#committee_phone_number')->attr('value'));
    }

    public function provideRegularAdherentsCredentials()
    {
        return [
            ['carl999@example.fr', 'secret!12345', '01 11 22 33 44'],
            ['luciole1989@spambox.fr', 'EnMarche2017', '07 27 36 36 43'],
        ];
    }

    public function testDocumentsActionSecured()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);
    }

    public function testDocumentsActionIsAccessibleAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Documents', $this->client->getResponse()->getContent());
    }

    public function testContactActionSecured()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);
    }

    public function testContactActionForAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');
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
        $this->assertContains('Votre message a bien été envoyé.', $crawler->filter('#notice-flashes')->text());

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(AdherentContactMessage::class));
    }

    public function testContactActionWithInvalidUuid()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

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
    public function testCannotTerminateMembership(string $email, string $password)
    {
        $this->authenticateAsAdherent($this->client, $email, $password);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(0, $crawler->filter('.settings_unsubscribe'));

        $this->client->request(Request::METHOD_GET, '/mon-compte/desadherer');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function dataProviderCannotTerminateMembership()
    {
        yield 'Host' => ['gisele-berthoux@caramail.com', 'ILoveYouManu'];
        yield 'Referent' => ['referent@en-marche-dev.fr', 'referent'];
        yield 'BoardMember' => ['carl999@example.fr', 'secret!12345'];
    }

    public function testAdherentTerminatesMembership()
    {
        /** @var Adherent $adherent */
        $adherentBeforeUnregistration = $this->getAdherentRepository()->findByEmail('michel.vasseur@example.ch');

        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $crawler->filter('.settings__delete_account'));

        $crawler = $this->client->click($crawler->selectLink('Supprimer mon adhésion et mon compte')->link());

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-adherent/mon-compte/desadherer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form([
            'unregistration' => [],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(1, $errors->count());
        $this->assertSame('Afin de confirmer la suppression de votre compte, veuillez sélectionner la raison pour laquelle vous quittez le mouvement.', $errors->eq(0)->text());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', 'en-marche-suisse'));

        $this->assertSame('3 adhérents', $crawler->filter('.committee-members')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/desadherer');
        $reasons = $this->client->getContainer()->getParameter('adherent_unregistration_reasons');
        $reasonsValues = array_keys($reasons);
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

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-adherent/mon-compte/desadherer', $this->client->getRequest()->getUri());

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(0, $errors->count());
        $this->assertSame('Votre adhésion et votre compte En Marche ont bien été supprimés et vos données personnelles effacées de notre base.', trim($crawler->filter('#is_not_adherent h1')->eq(0)->text()));

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(AdherentTerminateMembershipMessage::class, 'michel.vasseur@example.ch'));

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/comites/%s', 'en-marche-suisse'));

        $this->assertSame('2 adhérents', $crawler->filter('.committee-members')->text());

        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findByEmail('michel.vasseur@example.ch');

        $this->assertNull($adherent);

        /** @var Unregistration $unregistration */
        $unregistration = $this->getRepository(Unregistration::class)->findOneByUuid(LoadAdherentData::ADHERENT_13_UUID);

        $this->assertSame(array_values($chosenReasons), $unregistration->getReasons());
        $this->assertSame('Je me désinscris', $unregistration->getComment());
        $this->assertSame($adherentBeforeUnregistration->getRegisteredAt()->format('Y-m-d H:i:s'), $unregistration->getRegisteredAt()->format('Y-m-d H:i:s'));
        $this->assertSame((new \DateTime())->format('Y-m-d'), $unregistration->getUnregisteredAt()->format('Y-m-d'));
        $this->assertSame($adherentBeforeUnregistration->getUuid()->toString(), $unregistration->getUuid()->toString());
        $this->assertSame($adherentBeforeUnregistration->getPostalCode(), $unregistration->getPostalCode());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

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
}
