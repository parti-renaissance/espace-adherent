<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use App\Formation\FormationHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormationUploadFileController extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        Formation $formation,
        FormationHandler $formationHandler,
    ): Response {
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('Key "file" is required');
        }

        $errors = $validator->validate($uploadedFile, [new File(maxSize: '10M')]);

        if ($errors->count()) {
            return $this->json($errors, 400);
        }

        $formation->setFile($uploadedFile);

        $formationHandler->handleFile($formation);
        $entityManager->flush();

        return $this->json('OK');
    }
}
