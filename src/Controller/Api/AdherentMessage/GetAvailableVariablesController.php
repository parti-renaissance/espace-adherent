<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\Variable\Dictionary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class GetAvailableVariablesController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->json(Dictionary::getList());
    }
}
