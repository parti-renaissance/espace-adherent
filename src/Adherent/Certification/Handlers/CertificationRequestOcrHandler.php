<?php

namespace App\Adherent\Certification\Handlers;

use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationRequestDocumentManager;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Repository\AdherentRepository;
use App\Vision\ImageAnnotations;
use App\Vision\VisionHandler;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CertificationRequestOcrHandler implements CertificationRequestHandlerInterface
{
    private $em;
    private $adherentRepository;
    private $documentManager;
    private $storage;
    private $visionHandler;
    private $serializer;
    private $certificationAuthorityManager;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        CertificationRequestDocumentManager $documentManager,
        Filesystem $storage,
        VisionHandler $visionHandler,
        ObjectNormalizer $normalizer,
        CertificationAuthorityManager $certificationAuthorityManager
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->documentManager = $documentManager;
        $this->storage = $storage;
        $this->visionHandler = $visionHandler;
        $this->serializer = $normalizer;
        $this->certificationAuthorityManager = $certificationAuthorityManager;
    }

    public function supports(CertificationRequest $certificationRequest): bool
    {
        return $certificationRequest->isPending();
    }

    public function handle(CertificationRequest $certificationRequest): void
    {
        $imageAnnotations = $this->visionHandler->annotate($certificationRequest->getPathWithDirectory());

        $certificationRequest->setOcrPayload($this->normalizeImageAnnotations($imageAnnotations));

        $this->em->flush();

        if (!$imageAnnotations->isFrenchNationalIdentityCard()) {
            $certificationRequest->setOcrStatus(CertificationRequest::OCR_STATUS_PRE_REFUSED);
            $certificationRequest->setOcrResult(CertificationRequestRefuseCommand::REFUSAL_REASON_DOCUMENT_NOT_IN_CONFORMITY);

            $this->em->flush();

            return;
        }

        if (!$this->match($certificationRequest, $imageAnnotations)) {
            $certificationRequest->setOcrStatus(CertificationRequest::OCR_STATUS_PRE_REFUSED);
            $certificationRequest->setOcrResult(CertificationRequestRefuseCommand::REFUSAL_REASON_INFORMATIONS_NOT_MATCHING);

            $this->em->flush();

            return;
        }

        $certificationRequest->setOcrStatus(CertificationRequest::OCR_STATUS_PRE_APPROVED);

        $this->em->flush();
    }

    private function normalizeImageAnnotations(ImageAnnotations $imageAnnotations): array
    {
        return $this->serializer->normalize($imageAnnotations, 'array', ['groups' => ['ocr']]);
    }

    private function match(CertificationRequest $certificationRequest, ImageAnnotations $imageAnnotations): bool
    {
        $adherent = $certificationRequest->getAdherent();

        $imageAnnotations->getFirstName();
        $imageAnnotations->getLastName();
        $imageAnnotations->getBirthDate();

        return false !== mb_strpos($imageAnnotations->getFirstName(), $adherent->getFirstName())
            && false !== mb_strpos($imageAnnotations->getLastName(), $adherent->getLastName())
            && $adherent->getBirthDate()->format('d.m.Y') === $imageAnnotations->getBirthDate()
        ;
    }
}
