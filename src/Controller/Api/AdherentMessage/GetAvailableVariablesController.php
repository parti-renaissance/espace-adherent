<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class GetAvailableVariablesController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->json([
            [
                'label' => 'Chère/Cher Prénom',
                'code' => '{{Chère/Cher Prénom}}',
                'description' => null,
            ],
            [
                'label' => 'Prénom',
                'code' => '{{Prénom}}',
                'description' => null,
            ],
            [
                'label' => 'Nom',
                'code' => '{{Nom}}',
                'description' => null,
            ],
            [
                'label' => 'Numéro militant',
                'code' => '{{Numéro militant}}',
                'description' => null,
            ],
        ]);
    }
}
