<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DuplicateMessageController extends AbstractController
{
    public function __invoke(AdherentMessageManager $manager, AdherentMessage $data): Response
    {
        $manager->duplicate($data);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
