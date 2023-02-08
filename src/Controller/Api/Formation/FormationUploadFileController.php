<?php

namespace App\Controller\Api\Formation;

use App\Entity\AdherentFormation\Formation;
use App\Formation\FormationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FormationUploadFileController extends AbstractController
{
    public function __invoke(Request $request, Formation $formation, FormationHandler $formationHandler): Formation
    {
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('Key "file" is required');
        }

        $formation->setFile($uploadedFile);

        $formationHandler->handleFile($formation);

        return $formation;
    }
}
