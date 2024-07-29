<?php

namespace App\Controller\Api;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GeneralMeetingReportDownloadFileController extends AbstractController
{
    public function __construct(private readonly FilesystemOperator $defaultStorage, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, GeneralMeetingReport $generalMeetingReport): Response
    {
        $filePath = $generalMeetingReport->getFilePath();

        if (!$this->defaultStorage->has($filePath)) {
            $this->logger->error(\sprintf('No file found for GeneralMeetingReport with uuid "%s".', $generalMeetingReport->getUuid()->toString()));

            throw $this->createNotFoundException('File not found.');
        }

        $response = new Response($this->defaultStorage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $this->defaultStorage->mimeType($filePath),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (new Slugify())->slugify($generalMeetingReport->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
