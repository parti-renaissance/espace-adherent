<?php

namespace Tests\AppBundle\Adherent;

use AppBundle\Adherent\CertificationManager;
use AppBundle\Repository\AdherentRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CertificationManagerTest extends WebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CertificationManager
     */
    private $certificationManager;

    use ControllerTestTrait;

    /**
     * @dataProvider provideCanRequest
     */
    public function testCanRequest(string $email, bool $canRequest): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        self::assertSame($canRequest, $this->certificationManager->canRequest($adherent));
    }

    public function provideCanRequest(): iterable
    {
        yield ['jacques.picard@en-marche.fr', false];
        yield ['carl999@example.fr', false];
        yield ['luciole1989@spambox.fr', true];
        yield ['lolodie.dutemps@hotnix.tld', true];
        yield ['thomas.leclerc@example.ch', true];
    }

    /**
     * @dataProvider provideCreateRequest
     */
    public function testCreateRequest(string $email): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        self::assertFalse($adherent->isCertified());
        self::assertFalse($adherent->hasPendingCertificationRequest());

        $certificationRequest = $this->certificationManager->createRequest($adherent);

        self::assertTrue($adherent->hasPendingCertificationRequest());
        self::assertSame($certificationRequest, $adherent->getPendingCertificationRequest());
        self::assertTrue($certificationRequest->isPending());
        self::assertFalse($adherent->isCertified());
    }

    /**
     * @dataProvider provideCreateRequest
     * @depends testCreateRequest
     */
    public function testHandleRequest(string $email): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        $certificationRequest = $this->certificationManager->createRequest($adherent);
        $certificationRequest->setDocument(new UploadedFile(
            __DIR__.'/../../app/data/files/application_requests/curriculum/cv.pdf',
            'cv.pdf',
            'application/pdf',
            1234
        ));
        $this->certificationManager->handleRequest($certificationRequest);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertTrue($adherent->hasPendingCertificationRequest());
        self::assertSame($certificationRequest, $adherent->getPendingCertificationRequest());
        self::assertTrue($certificationRequest->isPending());
        self::assertNotNull($certificationRequest->getDocumentName());
        self::assertTrue($this->getStorage()->has($certificationRequest->getPathWithDirectory()));
    }

    public function provideCreateRequest(): iterable
    {
        yield ['luciole1989@spambox.fr'];
        yield ['lolodie.dutemps@hotnix.tld'];
        yield ['thomas.leclerc@example.ch'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->certificationManager = new CertificationManager($this->manager, $this->getStorage());
    }

    protected function tearDown()
    {
        $this->kill();

        $this->adherentRepository = null;
        $this->certificationAuthorityManager = null;

        parent::tearDown();
    }
}
