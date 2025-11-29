<?php

declare(strict_types=1);

namespace App\GeneralMeeting;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;

class GeneralMeetingReportHandler
{
    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    public function handleFile(GeneralMeetingReport $generalMeetingReport): void
    {
        if (!$file = $generalMeetingReport->getFile()) {
            return;
        }

        $this->removeFile($generalMeetingReport);

        $generalMeetingReport->setFilePath($path = \sprintf(
            '%s/%s.%s',
            'files/general_meeting_reports',
            Uuid::uuid4()->toString(),
            $file->getClientOriginalExtension()
        ));

        $this->defaultStorage->write($path, file_get_contents($file->getPathname()));

        $generalMeetingReport->setFile(null);
    }

    private function removeFile(GeneralMeetingReport $generalMeetingReport): void
    {
        if (!$filePath = $generalMeetingReport->getFilePath()) {
            return;
        }

        $generalMeetingReport->setFilePath(null);

        if (!$this->defaultStorage->has($filePath)) {
            return;
        }

        $this->defaultStorage->delete($filePath);
    }
}
