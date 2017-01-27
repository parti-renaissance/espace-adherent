<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailjet\Message\CommitteeCreationConfirmationMessage;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsSecured($profilePage)
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);
    }

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsAccessibleForAdherent($profilePage, $title)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.adherent_profile .adherent-profile-menu ul li a.active'));
        $this->assertSame($profilePage, $current->attr('href'));
        $this->assertSame('Carl Mirabeau', $crawler->filter('.adherent_profile > h2')->text());
        $this->assertSame('carl999@example.fr', $crawler->filter('.adherent_profile > p')->text());
        $this->assertSame($title, $crawler->filter('.adherent_profile h3')->text());
    }

    public function provideProfilePage()
    {
        yield ['/espace-adherent/mon-profil', 'Informations personnelles'];

        yield ['/espace-adherent/mon-profil/centres-d-interet', 'Centres d\'intérêt'];

        yield ['/espace-adherent/mon-profil/changer-mot-de-passe', 'Mot de passe'];

        yield ['/espace-adherent/mon-profil/preferences-des-email', 'Préférences des e-mails'];
    }

    public function testEditProfileFillAdherentData()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

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
        $this->assertSame('08/07/1950', $crawler->filter(sprintf($inputPattern, 'birthdate'))->attr('value'));

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
                'birthdate' => '!',
            ],
        ]));

        $errors = $crawler->filter('.form__errors > li');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(6, $errors->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        $this->assertSame('Cette ville et ce code postal ne sont pas liés.', $errors->eq(2)->text());
        $this->assertSame("Cette valeur n'est pas un identifiant valide de ville française.", $errors->eq(3)->text());
        $this->assertSame("L'adresse est obligatoire.", $errors->eq(4)->text());
        $this->assertSame("Cette valeur n'est pas valide.", $errors->eq(5)->text());

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
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => '27/10/1985',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertSame('Vos informations ont été mises à jour avec succès.', trim($crawler->filter('#notice-flashes')->text()));

        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');

        $this->assertSame('female', $adherent->getGender());
        $this->assertSame('Jean Dupont', $adherent->getFullName());
        $this->assertSame('9 rue du Lycée', $adherent->getAddress());
        $this->assertSame('06000', $adherent->getPostalCode());
        $this->assertSame('Nice', $adherent->getCityName());
        $this->assertSame('401020304', $adherent->getPhone()->getNationalNumber());
        $this->assertSame('student', $adherent->getPosition());
    }

    public function testEditAdherentInterests()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/centres-d-interet');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = '#app_adherent_pin_interests_interests > '.
                           'input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(16, $checkboxes = $crawler->filter($checkBoxPattern));

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

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil/centres-d-interet', $this->client);

        /* @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findByEmail('carl999@example.fr');

        $this->assertSame(array_values($chosenInterests), $adherent->getInterests());

        $crawler = $this->client->followRedirect();

        $this->assertCount(16, $checkboxes = $crawler->filter($checkBoxPattern));

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

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/changer-mot-de-passe');

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

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil/changer-mot-de-passe', $this->client);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'heaneaheah');
    }

    /**
     * @dataProvider provideCommitteesHostsAdherentsCredentials
     */
    public function testCommitteesAdherentsHostsAreNotAllowedToCreateNewCommittees(string $emaiLAddress, string $password)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emaiLAddress, $password);
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
            'Benjamin Duroc created a committee that is still under approval' => [
                'benjyd@aol.com',
                'HipHipHip',
            ],
        ];
    }

    /**
     * @dataProvider provideRegularAdherentsCredentials
     */
    public function testRegularAdherentCanCreateOneNewCommittee(string $emaiLAddress, string $password)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emaiLAddress, $password);
        $crawler = $this->client->click($crawler->selectLink('Créer un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'facebookPageUrl' => 'yo',
                'twitterNickname' => '@!!',
                'googlePlusPageUrl' => 'yo',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(10, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame('Cette ville et ce code postal ne sont pas liés.', $crawler->filter('#committee-address > .form__errors > li')->eq(0)->text());
        $this->assertSame("Cette valeur n'est pas un identifiant valide de ville française.", $crawler->filter('#committee-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#field-address > .form__errors > li')->text());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 5 caractères.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-googleplus-page-url > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'committee' => [
                'name' => 'Lyon est En Marche !',
                'description' => 'Comité français En Marche ! de la ville de Lyon',
                'address' => [
                    'country' => 'FR',
                    'address' => '6 rue Neyret',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
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
        $this->assertCount(1, $this->emailRepository->findMessages(CommitteeCreationConfirmationMessage::class, $emaiLAddress));

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Votre comité a été créé avec succès. Il est néanmoins en attente de validation par un administrateur', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Lyon est En Marche !', $crawler->filter('#committee-name')->text());
        $this->assertSame('Comité français En Marche ! de la ville de Lyon', $crawler->filter('#committee-description')->text());
    }

    public function provideRegularAdherentsCredentials()
    {
        return [
            ['carl999@example.fr', 'secret!12345'],
            ['luciole1989@spambox.fr', 'EnMarche2017'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->emailRepository = null;
        $this->committeeRepository = null;
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }
}
