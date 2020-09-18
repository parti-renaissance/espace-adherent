<?php

namespace App\Adherent\Certification\Handlers;

use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationRequestDocumentManager;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Repository\AdherentRepository;
use App\Vision\VisionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
use League\Flysystem\Filesystem;

class CertificationRequestOcrHandler implements CertificationRequestHandlerInterface
{
    private $em;
    private $adherentRepository;
    private $documentManager;
    private $storage;
    private $visionHandler;
    private $certificationAuthorityManager;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        CertificationRequestDocumentManager $documentManager,
        Filesystem $storage,
        VisionHandler $visionHandler,
        CertificationAuthorityManager $certificationAuthorityManager
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->documentManager = $documentManager;
        $this->storage = $storage;
        $this->visionHandler = $visionHandler;
        $this->certificationAuthorityManager = $certificationAuthorityManager;
    }

    public function supports(CertificationRequest $certificationRequest): bool
    {
        return $certificationRequest->isPending();
    }

    public function handle(CertificationRequest $certificationRequest): void
    {
        $filePath = $certificationRequest->getPathWithDirectory();

        if (!$this->visionHandler->isFrenchNationalIdentityCard($filePath)) {
            $certificationRequest->refuse(
                CertificationRequestRefuseCommand::REFUSAL_REASON_OTHER,
                'Le document envoyé n\'est pas detecté comme une carte d\'identité Française'
            );
            $certificationRequest->process();

            $this->em->flush();

            return;
        }

        $this->certificationAuthorityManager->approve($certificationRequest);
    }
}
