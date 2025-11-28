<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Entity\QrCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/qr-code/{uuid}', name: 'app_qr_code', methods: 'GET')]
class QrCodeController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(QrCode $qrCode): Response
    {
        $qrCode->increment();

        $this->entityManager->flush();

        return new RedirectResponse($qrCode->getRedirectUrl());
    }
}
