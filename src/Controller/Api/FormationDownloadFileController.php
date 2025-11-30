<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use App\Utils\HttpUtils;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;

class FormationDownloadFileController extends AbstractFormationContentController
{
    public function __invoke(
        FilesystemOperator $defaultStorage,
        Formation $formation,
    ): Response {
        if (!$formation->isFileContent()) {
            throw $this->createNotFoundException('Formation has no file.');
        }

        $this->printFormation($formation);

        $filePath = $formation->getFilePath();

        return HttpUtils::createResponse(
            $defaultStorage,
            $filePath,
            new Slugify()->slugify($formation->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
        );
    }
}
