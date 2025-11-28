<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IncrementRiposteStatsCounterController extends AbstractController
{
    public function __invoke(Riposte $riposte, string $action, EntityManagerInterface $entityManager): JsonResponse
    {
        switch ($action) {
            case Riposte::ACTION_DETAIL_VIEW:
                $riposte->incrementNbDetailViews();

                break;
            case Riposte::ACTION_SOURCE_VIEW:
                $riposte->incrementNbSourceViews();

                break;
            case Riposte::ACTION_RIPOSTE:
                $riposte->incrementNbRipostes();

                break;
            default:
                return $this->json([
                    'code' => 'unknown_action',
                    'message' => 'L\'action n\'est pas reconnue.',
                ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json('OK');
    }
}
