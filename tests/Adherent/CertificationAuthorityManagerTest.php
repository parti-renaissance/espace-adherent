<?php

namespace Tests\App\Adherent;

use App\Adherent\CertificationAuthorityManager;
use App\Repository\AdherentRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group certification
 */
class CertificationAuthorityManagerTest extends WebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CertificationAuthorityManager
     */
    private $certificationAuthorityManager;

    use ControllerTestTrait;

    public function testCertify(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('lolodie.dutemps@hotnix.tld');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $this->certificationAuthorityManager->certify($adherent, $administrator);

        $this->manager->refresh($adherent);

        self::assertTrue($adherent->isCertified());
    }

    public function testUncertify(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('michelle.dufour@example.ch');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertTrue($adherent->isCertified());

        $this->certificationAuthorityManager->uncertify($adherent, $administrator);

        $this->manager->refresh($adherent);

        self::assertFalse($adherent->isCertified());
    }

    public function testApprove(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getCertificationRequests()->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $this->certificationAuthorityManager->approve($certificationRequest, $administrator);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertTrue($adherent->isCertified());
        self::assertTrue($certificationRequest->isApproved());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
    }

    public function testRefuse(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getCertificationRequests()->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $this->certificationAuthorityManager->refuse($certificationRequest, $administrator);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertFalse($adherent->isCertified());
        self::assertTrue($certificationRequest->isRefused());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->certificationAuthorityManager = new CertificationAuthorityManager($this->manager);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->adherentRepository = null;
        $this->certificationAuthorityManager = null;

        parent::tearDown();
    }
}
