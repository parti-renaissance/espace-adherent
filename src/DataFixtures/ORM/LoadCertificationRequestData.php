<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Adherent\CertificationAuthorityManager;
use AppBundle\Adherent\CertificationManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\CertificationRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCertificationRequestData extends Fixture
{
    private $certificationManager;
    private $certificationAuthorityManager;

    public function load(ObjectManager $manager): void
    {
        $this->certificationManager = new CertificationManager($manager, $this->getStorage());
        $this->certificationAuthorityManager = new CertificationAuthorityManager($manager);

        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1');
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2');
        /** @var Adherent $adherent3 */
        $adherent3 = $this->getReference('adherent-3');
        /** @var Adherent $adherent4 */
        $adherent4 = $this->getReference('adherent-4');

        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-2');

        // Adherent certified without certification request
        $adherent1->certify();

        // Adherent with pending certification request
        $this->createRequest($adherent2);

        // Adherent with refused then approved certification request
        $certificationRequest = $this->createRequest($adherent3);
        $this->certificationAuthorityManager->refuse($certificationRequest, $administrator);

        $certificationRequest = $this->createRequest($adherent3);
        $this->certificationAuthorityManager->approve($certificationRequest, $administrator);

        // Adherent with refused certification request
        $certificationRequest = $this->createRequest($adherent4);
        $this->certificationAuthorityManager->refuse($certificationRequest, $administrator);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }

    private function createDocument(): UploadedFile
    {
        return new UploadedFile(
            __DIR__.'/../../../app/data/files/certification_requests/document/CNI.jpg',
            'CNI.jpg',
            'image/jpeg',
            1234
        );
    }

    private function createRequest(Adherent $adherent): CertificationRequest
    {
        $certificationRequest = $this->certificationManager->createRequest($adherent);
        $certificationRequest->setDocument($this->createDocument());
        $this->certificationManager->handleRequest($certificationRequest);

        return $certificationRequest;
    }

    private function getStorage(): Filesystem
    {
        return  $this->container->get('app.storage');
    }
}
