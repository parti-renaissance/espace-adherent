<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DuplicateMessageController extends AbstractController
{
    public function __invoke(AdherentMessageManager $manager, AdherentMessage $data): JsonResponse
    {
        $clone = $manager->duplicate($data);

        return new JsonResponse(['uuid' => $clone->getUuid()->toString()], Response::HTTP_CREATED);
    }
}
