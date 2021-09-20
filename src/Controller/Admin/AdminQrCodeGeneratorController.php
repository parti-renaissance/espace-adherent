<?php

namespace App\Controller\Admin;

use App\Entity\QrCode;
use App\QrCode\QrCodeEntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/qr-code/{uuid}/generate", name="app_admin_qr_code_generate", methods="GET")
 * @Security("is_granted('ROLE_ADMIN_QR_CODES')")
 */
class AdminQrCodeGeneratorController
{
    private $qrCodeEntityHandler;

    public function __construct(QrCodeEntityHandler $qrCodeEntityHandler)
    {
        $this->qrCodeEntityHandler = $qrCodeEntityHandler;
    }

    public function __invoke(QrCode $qrCode): Response
    {
        return $this->qrCodeEntityHandler->generateQrCode($qrCode);
    }
}
