<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\Certification\CertificationManager;
use App\Adherent\Certification\CertificationRequestBlockCommand;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use App\Entity\Reporting\AdherentCertificationHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCertificationData extends Fixture implements DependentFixtureInterface
{
    private $certificationManager;

    public function __construct(CertificationManager $certificationManager)
    {
        $this->certificationManager = $certificationManager;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1', Adherent::class);
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2', Adherent::class);
        /** @var Adherent $adherent3 */
        $adherent3 = $this->getReference('adherent-3', Adherent::class);
        /** @var Adherent $adherent4 */
        $adherent4 = $this->getReference('adherent-4', Adherent::class);
        /** @var Adherent $adherent5 */
        $adherent5 = $this->getReference('adherent-5', Adherent::class);
        /** @var Adherent $adherent6 */
        $adherent6 = $this->getReference('senatorial-candidate', Adherent::class);

        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-2', Administrator::class);

        // Adherent certified without certification request
        $adherent1->certify();
        $manager->persist(AdherentCertificationHistory::createCertify($adherent1, $administrator));

        $adherent6->certify();
        $manager->persist(AdherentCertificationHistory::createCertify($adherent6, $administrator));

        // Adherent with pending certification request
        $manager->persist($this->createRequest($adherent2));

        // Adherent with refused then approved certification request
        $manager->persist($certificationRequest = $this->createRequest($adherent3));
        $refuseCommand = new CertificationRequestRefuseCommand($certificationRequest, $administrator);
        $refuseCommand->setReason(CertificationRequestRefuseCommand::REFUSAL_REASON_INFORMATIONS_NOT_MATCHING);
        $refuseCommand->setComment('Last names do not match.');

        $certificationRequest->refuse(
            $refuseCommand->getReason(),
            $refuseCommand->getCustomReason(),
            $refuseCommand->getComment()
        );
        $certificationRequest->process($refuseCommand->getAdministrator());

        $manager->persist($certificationRequest = $this->createRequest($adherent3));
        $certificationRequest->approve();
        $certificationRequest->process($administrator);
        $adherent3->certify();
        $manager->persist(AdherentCertificationHistory::createCertify($adherent1, $administrator));

        // Adherent with 2 refused certification request
        $manager->persist($certificationRequest = $this->createRequest($adherent4));
        $refuseCommand = new CertificationRequestRefuseCommand($certificationRequest, $administrator);
        $refuseCommand->setReason(CertificationRequestRefuseCommand::REFUSAL_REASON_DOCUMENT_NOT_READABLE);
        $refuseCommand->setComment('Informations are not readable.');

        $certificationRequest->refuse(
            $refuseCommand->getReason(),
            $refuseCommand->getCustomReason(),
            $refuseCommand->getComment()
        );
        $certificationRequest->process($refuseCommand->getAdministrator());

        $manager->persist($certificationRequest = $this->createRequest($adherent4));
        $refuseCommand = new CertificationRequestRefuseCommand($certificationRequest, $administrator);
        $refuseCommand->setReason(CertificationRequestRefuseCommand::REFUSAL_REASON_INFORMATIONS_NOT_MATCHING);
        $refuseCommand->setComment('First names do not match.');
        $certificationRequest->refuse(
            $refuseCommand->getReason(),
            $refuseCommand->getCustomReason(),
            $refuseCommand->getComment()
        );
        $certificationRequest->process($refuseCommand->getAdministrator());

        // Adherent with blocked certification request
        $manager->persist($certificationRequest = $this->createRequest($adherent5));
        $blockCommand = new CertificationRequestBlockCommand($certificationRequest, $administrator);
        $blockCommand->setReason(CertificationRequestBlockCommand::BLOCK_REASON_FALSE_DOCUMENT);
        $blockCommand->setComment('French ID should have blue borders.');
        $certificationRequest->block(
            $blockCommand->getReason(),
            $blockCommand->getCustomReason(),
            $blockCommand->getComment()
        );
        $certificationRequest->process($blockCommand->getAdministrator());

        $manager->flush();
    }

    public function getDependencies(): array
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

        return $certificationRequest;
    }
}
