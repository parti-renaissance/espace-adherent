<?php

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use App\Utils\HttpUtils;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormationDownloadFileController extends AbstractController
{
    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    public function __invoke(Request $request, Formation $formation): Response
    {
        $filePath = $formation->getFilePath();

        return HttpUtils::createResponse(
            $this->defaultStorage,
            $filePath,
            (new Slugify())->slugify($formation->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
        );
    }
}
