<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Normalizer\ImageExposeNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DuplicateMessageController extends AbstractController
{
    public function __invoke(AdherentMessageManager $manager, AdherentMessage $data): Response
    {
        $clone = $manager->duplicate($data);

        return $this->json(
            $clone,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['message_read', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        );
    }
}
