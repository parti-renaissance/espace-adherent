<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\Utils\HttpUtils;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralMeetingReportDownloadFileController extends AbstractController
{
    public function __construct(private readonly FilesystemOperator $defaultStorage, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, GeneralMeetingReport $generalMeetingReport): Response
    {
        $filePath = $generalMeetingReport->getFilePath();

        return HttpUtils::createResponse(
            $this->defaultStorage,
            $filePath,
            new Slugify()->slugify($generalMeetingReport->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
        );
    }
}
