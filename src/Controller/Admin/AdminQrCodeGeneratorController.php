<?php

namespace App\Controller\Admin;

use App\Entity\QrCode;
use App\QrCode\QrCodeEntityHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN_QR_CODES')]
#[Route(path: '/qr-code/{uuid}/generate.{_format}', name: 'app_admin_qr_code_generate', methods: 'GET', defaults: ['_format' => 'png'], requirements: ['_format' => 'png|svg'])]
class AdminQrCodeGeneratorController
{
    private $qrCodeEntityHandler;

    public function __construct(QrCodeEntityHandler $qrCodeEntityHandler)
    {
        $this->qrCodeEntityHandler = $qrCodeEntityHandler;
    }

    public function __invoke(Request $request, QrCode $qrCode, string $_format): Response
    {
        return $this->qrCodeEntityHandler->generateQrCode($qrCode, $_format, $request->query->has('_download'));
    }
}
