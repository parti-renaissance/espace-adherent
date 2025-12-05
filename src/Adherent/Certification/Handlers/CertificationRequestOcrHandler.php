<?php

declare(strict_types=1);

namespace App\Adherent\Certification\Handlers;

use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Image\PdfToImageConverter;
use App\Vision\IdentityDocumentParser;
use App\Vision\ImageAnnotations;
use App\Vision\VisionHandler;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CertificationRequestOcrHandler implements CertificationRequestHandlerInterface
{
    private $em;
    private $visionHandler;
    private $serializer;
    private $pdfToImageConverter;
    private $storage;
    private $identityDocumentParser;

    public function __construct(
        EntityManagerInterface $em,
        VisionHandler $visionHandler,
        #[Autowire(service: 'serializer.normalizer.object')]
        ObjectNormalizer $normalizer,
        PdfToImageConverter $pdfToImageConverter,
        FilesystemOperator $defaultStorage,
        IdentityDocumentParser $identityDocumentParser,
    ) {
        $this->em = $em;
        $this->visionHandler = $visionHandler;
        $this->serializer = $normalizer;
        $this->pdfToImageConverter = $pdfToImageConverter;
        $this->storage = $defaultStorage;
        $this->identityDocumentParser = $identityDocumentParser;
    }

    public function getPriority(): int
    {
        return -255;
    }

    public function supports(CertificationRequest $certificationRequest): bool
    {
        return $certificationRequest->isPending()
            && $this->isDocumentReadableByOcr($certificationRequest);
    }

    public function handle(CertificationRequest $certificationRequest): void
    {
        if ($certificationRequest->isPdfDocument()) {
            $content = $this->pdfToImageConverter->getRawImageFromPdf($this->storage->read($certificationRequest->getPathWithDirectory()));
        } else {
            $content = $this->storage->read($certificationRequest->getPathWithDirectory());
        }

        $imageAnnotations = $this->visionHandler->annotate($content);

        $certificationRequest->setOcrPayload($this->normalizeImageAnnotations($imageAnnotations));

        $this->em->flush();

        if (!$imageAnnotations->isSupportedIdentityDocument()) {
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
        return $this->identityDocumentParser->match($certificationRequest->getAdherent(), $imageAnnotations);
    }

    private function isDocumentReadableByOcr(CertificationRequest $certificationRequest): bool
    {
        return \in_array($certificationRequest->getDocumentMimeType(), CertificationRequest::MIME_TYPES, true);
    }
}
