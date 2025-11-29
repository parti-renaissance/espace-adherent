<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Certification;

use App\Adherent\Certification\CertificationManager;
use App\Repository\AdherentRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
#[Group('certification')]
class CertificationManagerTest extends AbstractKernelTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CertificationManager
     */
    private $certificationManager;

    #[DataProvider('provideCreateRequest')]
    public function testCreateRequest(string $email): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        self::assertFalse($adherent->isCertified());
        self::assertFalse($adherent->getCertificationRequests()->hasPendingCertificationRequest());

        $certificationRequest = $this->certificationManager->createRequest($adherent);

        self::assertTrue($adherent->getCertificationRequests()->hasPendingCertificationRequest());
        self::assertSame($certificationRequest, $adherent->getCertificationRequests()->getPendingCertificationRequest());
        self::assertTrue($certificationRequest->isPending());
        self::assertFalse($adherent->isCertified());
    }

    #[DataProvider('provideCreateRequest')]
    #[Depends('testCreateRequest')]
    public function testHandleRequest(string $email): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        $certificationRequest = $this->certificationManager->createRequest($adherent);
        $certificationRequest->setDocument(new UploadedFile(
            __DIR__.'/../../../app/data/files/application_requests/curriculum/cv.pdf',
            'cv.pdf',
            'application/pdf',
            1234
        ));
        $this->certificationManager->handleRequest($certificationRequest);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertTrue($adherent->getCertificationRequests()->hasPendingCertificationRequest());
        self::assertSame($certificationRequest, $adherent->getCertificationRequests()->getPendingCertificationRequest());
        self::assertTrue($certificationRequest->isPending());
        self::assertNotNull($certificationRequest->getDocumentName());
        self::assertTrue($this->getStorage()->has($certificationRequest->getPathWithDirectory()));
    }

    public static function provideCreateRequest(): iterable
    {
        yield ['luciole1989@spambox.fr'];
        yield ['lolodie.dutemps@hotnix.tld'];
        yield ['thomas.leclerc@example.ch'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->certificationManager = $this->get(CertificationManager::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->certificationManager = null;

        parent::tearDown();
    }
}
