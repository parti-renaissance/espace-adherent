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
    private $em;
    private $visionHandler;
    private $serializer;

    public function __construct(EntityManagerInterface $em, VisionHandler $visionHandler, ObjectNormalizer $normalizer)
    {
        $this->em = $em;
        $this->visionHandler = $visionHandler;
        $this->serializer = $normalizer;
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

        $firstNames = array_map(function (string $firstName) {
            return trim($firstName);
        }, explode(',', $imageAnnotations->getFirstName()));

        return \in_array(mb_strtoupper($adherent->getFirstName()), $firstNames, true)
            && mb_strtoupper($adherent->getLastName()) === $imageAnnotations->getLastName()
            && $adherent->getBirthDate()->format('d.m.Y') === $imageAnnotations->getBirthDate()
        ;
    }
}
