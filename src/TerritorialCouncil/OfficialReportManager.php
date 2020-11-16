<?php

namespace App\TerritorialCouncil;

use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\OfficialReportDocument;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OfficialReportManager
{
    private $storage;
    private $entityManager;

    public function __construct(FilesystemInterface $storage, EntityManagerInterface $entityManager)
    {
        $this->storage = $storage;
        $this->entityManager = $entityManager;
    }

    public function handleRequest(OfficialReport $report): void
    {
        // if new report
        if (null === $report->getId()
            && $president = $report->getPoliticalCommittee()->getTerritorialCouncil()->getMemberships()->getPresident()) {
            $report->setAuthor($president->getAdherent());
        }

        if ($report->getFile() instanceof UploadedFile) {
            $version = $report->getLastVersion() ?: 0;
            $filename = \sprintf('%s.%s',
                md5(\sprintf('%s@%s@%s', $report->getUuid(), $report->getFile()->getClientOriginalName(), $version)),
                $report->getFile()->getClientOriginalExtension()
            );
            $document = new OfficialReportDocument(
                $report,
                $filename,
                $report->getFile()->getClientOriginalExtension(),
                $report->getFile()->getMimeType(),
                ++$version
            );

            $this->upload($report, $document->getFilePathWithDirectory());
            $report->addDocument($document);

            $this->entityManager->persist($document);
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }

    public function upload(OfficialReport $report, string $path): void
    {
        if (!$report->getFile() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $this->storage->put($path, file_get_contents($report->getFile()->getPathname()));
    }
}
