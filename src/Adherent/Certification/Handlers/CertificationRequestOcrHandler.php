<?php

namespace App\Adherent\Certification\Handlers;

use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Vision\ImageAnnotations;
use App\Vision\VisionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CertificationRequestOcrHandler implements CertificationRequestHandlerInterface
{
    private const READABLE_DOCUMENT_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];

    private $em;
    private $visionHandler;
    private $serializer;

    public function __construct(EntityManagerInterface $em, VisionHandler $visionHandler, ObjectNormalizer $normalizer)
    {
        $this->em = $em;
        $this->visionHandler = $visionHandler;
        $this->serializer = $normalizer;
    }

    public function getPriority(): int
    {
        return -255;
    }

    public function supports(CertificationRequest $certificationRequest): bool
    {
        return $certificationRequest->isPending()
            && $this->isDocumentReadableByOcr($certificationRequest)
        ;
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

        $firstNames = array_map('trim', $imageAnnotations->getFirstNames());
        $birthDate = $imageAnnotations->getBirthDate();

        return \in_array(mb_strtoupper($adherent->getFirstName()), $firstNames, true)
            && mb_strtoupper($adherent->getLastName()) === mb_strtoupper($imageAnnotations->getLastName())
            && $birthDate
            && $adherent->getBirthDate()->format('Y-m-d') === $birthDate->format('Y-m-d')
        ;
    }

    private function isDocumentReadableByOcr(CertificationRequest $certificationRequest): bool
    {
        return \in_array($certificationRequest->getDocumentMimeType(), self::READABLE_DOCUMENT_MIME_TYPES, true);
    }
}
