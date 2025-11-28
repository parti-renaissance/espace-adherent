<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\GeneralMeeting\GeneralMeetingReportHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GeneralMeetingReportUploadFileController extends AbstractController
{
    public function __invoke(
        Request $request,
        GeneralMeetingReport $generalMeetingReport,
        GeneralMeetingReportHandler $generalMeetingReportHandler,
    ): GeneralMeetingReport {
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $generalMeetingReport->setFile($uploadedFile);

        $generalMeetingReportHandler->handleFile($generalMeetingReport);

        return $generalMeetingReport;
    }
}
