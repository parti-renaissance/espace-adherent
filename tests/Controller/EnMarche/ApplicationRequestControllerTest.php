<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\TechnicalSkill;
use AppBundle\Entity\ApplicationRequest\Theme;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\TechnicalSkillRepository;
use AppBundle\Repository\ApplicationRequest\ThemeRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ApplicationRequestControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var RunningMateRequestRepository
     */
    private $runningMateRequestRepository;

    /**
     * @var VolunteerRequestRepository
     */
    private $volunteerRequestRepository;

    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    /**
     * @var TechnicalSkillRepository
     */
    private $technicalSkillRepository;

    public function testRunningMateApplicationRequest(): void
    {
        /** @var Theme $theme1 */
        $theme1 = $this->themeRepository->findOneBy(['name' => 'Urbanisme']);
        /** @var Theme $theme2 */
        $theme2 = $this->themeRepository->findOneBy(['name' => 'Sécurité']);

        $crawler = $this->client->request('GET', '/appel-a-engagement');
        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $form = $crawler->filter('form[name="running_mate_request"]')->form();

        $this->client->request('POST', '/appel-a-engagement', [
            'running_mate_request' => [
                'gender' => 'male',
                'firstName' => 'Enzo',
                'lastName' => 'Colistier',
                'emailAddress' => 'enzo@colistiers.com',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0612345678',
                ],
                'address' => [
                    'address' => '8 Rue Enzo Godeas',
                    'cityName' => 'Toulouse',
                    'postalCode' => '31100',
                    'city' => '31100-31555',
                    'country' => 'FR',
                ],
                'favoriteCities' => ['06088', '44109'],
                'profession' => 'developer',
                'favoriteThemes' => [
                    $theme1->getId(),
                    $theme2->getId(),
                ],
                'customFavoriteTheme' => '',
                'isLocalAssociationMember' => '0',
                'localAssociationDomain' => '',
                'isPoliticalActivist' => '0',
                'politicalActivistDetails' => '',
                'isPreviousElectedOfficial' => '0',
                'previousElectedOfficialDetails' => '',
                'favoriteThemeDetails' => 'my favorite theme details',
                'projectDetails' => 'my project details',
                'professionalAssets' => 'my professional assets',
                'agreeToLREMValues' => true,
                'agreeToDataUse' => true,
                '_token' => $form['running_mate_request[_token]']->getValue(),
            ],
        ], [
            'running_mate_request' => [
                'curriculum' => new UploadedFile(
                    __DIR__.'/../../../app/data/storage/private/application_requests/running_mates/cv.pdf',
                    'cv.pdf',
                    'application/pdf',
                    1234
                ),
            ],
        ]);
        static::assertClientIsRedirectedTo('/appel-a-engagement/merci', $this->client);

        $this->client->followRedirect();
        static::assertResponseStatusCode(200, $this->client->getResponse());

        /** @var RunningMateRequest $runningMateRequest */
        $runningMateRequest = $this->runningMateRequestRepository->findOneBy(['emailAddress' => 'enzo@colistiers.com']);

        static::assertInstanceOf(RunningMateRequest::class, $runningMateRequest);
        static::assertSame('male', $runningMateRequest->getGender());
        static::assertSame('Enzo', $runningMateRequest->getFirstName());
        static::assertSame('Colistier', $runningMateRequest->getLastName());
        static::assertSame('enzo@colistiers.com', $runningMateRequest->getEmailAddress());
        static::assertSame(33, $runningMateRequest->getPhone()->getCountryCode());
        static::assertSame('612345678', $runningMateRequest->getPhone()->getNationalNumber());
        static::assertSame('8 Rue Enzo Godeas', $runningMateRequest->getAddress());
        static::assertSame('Toulouse', $runningMateRequest->getCityName());
        static::assertSame('31100', $runningMateRequest->getPostalCode());
        static::assertSame('31100-31555', $runningMateRequest->getCity());
        static::assertSame('FR', $runningMateRequest->getCountry());
        static::assertSame(['06088', '44109'], $runningMateRequest->getFavoriteCities());
        static::assertSame('developer', $runningMateRequest->getProfession());
        static::assertSame([$theme1, $theme2], $runningMateRequest->getFavoriteThemes()->toArray());
        static::assertNull($runningMateRequest->getCustomFavoriteTheme());
        static::assertFalse($runningMateRequest->isLocalAssociationMember());
        static::assertNull($runningMateRequest->getLocalAssociationDomain());
        static::assertFalse($runningMateRequest->isPoliticalActivist());
        static::assertNull($runningMateRequest->getPoliticalActivistDetails());
        static::assertFalse($runningMateRequest->isPreviousElectedOfficial());
        static::assertNull($runningMateRequest->getPreviousElectedOfficialDetails());
        static::assertSame('my favorite theme details', $runningMateRequest->getFavoriteThemeDetails());
        static::assertSame('my project details', $runningMateRequest->getProjectDetails());
        static::assertSame('my professional assets', $runningMateRequest->getProfessionalAssets());
        static::assertTrue(file_exists(__DIR__.'/../../../app/data/storage/private/'.$runningMateRequest->getPathWithDirectory()));
    }

    public function testVolunteerApplicationRequest(): void
    {
        /** @var Theme $theme1 */
        $theme1 = $this->themeRepository->findOneBy(['name' => 'Urbanisme']);
        /** @var Theme $theme2 */
        $theme2 = $this->themeRepository->findOneBy(['name' => 'Sécurité']);

        /** @var TechnicalSkill $technicalSkill1 */
        $technicalSkill1 = $this->technicalSkillRepository->findOneBy(['name' => 'Communication']);
        /** @var TechnicalSkill $technicalSkill2 */
        $technicalSkill2 = $this->technicalSkillRepository->findOneBy(['name' => 'Management']);

        $crawler = $this->client->request('GET', '/appel-a-engagement');
        static::assertResponseStatusCode(200, $this->client->getResponse());

        $form = $crawler->filter('form[name="volunteer_request"]')->form();

        $this->client->request('POST', '/appel-a-engagement', [
            'volunteer_request' => [
                'gender' => 'other',
                'firstName' => 'Remi',
                'lastName' => 'Zoo',
                'emailAddress' => 'remi@white.com',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0612345678',
                ],
                'address' => [
                    'address' => '8 Rue Enzo Godeas',
                    'cityName' => 'Toulouse',
                    'postalCode' => '31100',
                    'city' => '31100-31555',
                    'country' => 'FR',
                ],
                'favoriteCities' => ['06088', '44109'],
                'profession' => 'thief',
                'favoriteThemes' => [
                    $theme1->getId(),
                    $theme2->getId(),
                ],
                'customFavoriteTheme' => '',
                'technicalSkills' => [
                    $technicalSkill1->getId(),
                    $technicalSkill2->getId(),
                ],
                'customTechnicalSkills' => '',
                'isPreviousCampaignMember' => '0',
                'previousCampaignDetails' => '',
                'shareAssociativeCommitment' => '0',
                'associativeCommitmentDetails' => '',
                'agreeToLREMValues' => true,
                'agreeToDataUse' => true,
                '_token' => $form['volunteer_request[_token]']->getValue(),
            ],
        ]);
        static::assertClientIsRedirectedTo('/appel-a-engagement/merci', $this->client);

        $this->client->followRedirect();
        static::assertResponseStatusCode(200, $this->client->getResponse());

        /** @var VolunteerRequest $volunteerRequest */
        $volunteerRequest = $this->volunteerRequestRepository->findOneBy(['emailAddress' => 'remi@white.com']);

        static::assertInstanceOf(VolunteerRequest::class, $volunteerRequest);
        static::assertSame('other', $volunteerRequest->getGender());
        static::assertSame('Remi', $volunteerRequest->getFirstName());
        static::assertSame('Zoo', $volunteerRequest->getLastName());
        static::assertSame('remi@white.com', $volunteerRequest->getEmailAddress());
        static::assertSame(33, $volunteerRequest->getPhone()->getCountryCode());
        static::assertSame('612345678', $volunteerRequest->getPhone()->getNationalNumber());
        static::assertSame('8 Rue Enzo Godeas', $volunteerRequest->getAddress());
        static::assertSame('Toulouse', $volunteerRequest->getCityName());
        static::assertSame('31100', $volunteerRequest->getPostalCode());
        static::assertSame('31100-31555', $volunteerRequest->getCity());
        static::assertSame('FR', $volunteerRequest->getCountry());
        static::assertSame(['06088', '44109'], $volunteerRequest->getFavoriteCities());
        static::assertSame('thief', $volunteerRequest->getProfession());
        static::assertSame([$theme1, $theme2], $volunteerRequest->getFavoriteThemes()->toArray());
        static::assertNull($volunteerRequest->getCustomFavoriteTheme());
        static::assertSame([$technicalSkill1, $technicalSkill2], $volunteerRequest->getTechnicalSkills()->toArray());
        static::assertNull($volunteerRequest->getCustomTechnicalSkills());
        static::assertFalse($volunteerRequest->isPreviousCampaignMember());
        static::assertNull($volunteerRequest->getPreviousCampaignDetails());
        static::assertFalse($volunteerRequest->getShareAssociativeCommitment());
        static::assertNull($volunteerRequest->getAssociativeCommitmentDetails());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->runningMateRequestRepository = $this->getRepository(RunningMateRequest::class);
        $this->volunteerRequestRepository = $this->getRepository(VolunteerRequest::class);
        $this->themeRepository = $this->getRepository(Theme::class);
        $this->technicalSkillRepository = $this->getRepository(TechnicalSkill::class);
    }

    protected function tearDown()
    {
        $this->runningMateRequestRepository = null;
        $this->volunteerRequestRepository = null;
        $this->themeRepository = null;
        $this->technicalSkillRepository = null;

        $this->kill();

        parent::tearDown();
    }
}
