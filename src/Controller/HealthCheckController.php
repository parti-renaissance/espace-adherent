<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthCheckController extends AbstractController
{
    public function __invoke(Connection $connection): JsonResponse
    {
        try {
            $connection->executeQuery('SELECT 1');

            return new JsonResponse(['status' => 'OK']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'KO', 'error' => $e->getMessage()], 500);
        }
    }
}
