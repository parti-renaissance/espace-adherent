<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Ses\Rendering\SesMessageAssembler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/publications/{uuid}',
    name: 'app_renaissance_adherent_message_preview',
    requirements: ['uuid' => '%pattern_uuid%'],
    methods: ['GET'],
)]
class PreviewController extends AbstractController
{
    public function __invoke(AdherentMessage $message, SesMessageAssembler $assembler): Response
    {
        if ($message->isSent() && !$this->isGranted('ROLE_ADMIN_DASHBOARD')) {
            throw $this->createNotFoundException();
        }

        $response = new Response($assembler->assemble($message)->html);

        $response->headers->set('Content-Security-Policy', "script-src 'none'");
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
