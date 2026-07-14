<?php

declare(strict_types=1);

namespace App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Routes GET /parametres/confidentialite (affichage) + POST /parametres/confidentialite/toggle.
 * Host pattern Symfony natif : route param {marque} + requirements regex
 * (spec §12 + review Opus I1).
 *
 * Toggle set le cookie scopé root-domain + capture consent event server-side.
 */
final class ConsentSettingsController extends AbstractController
{
    public function __construct(
        private readonly ConsentCookieHelper $cookieHelper,
        private readonly PostHogService $service,
    ) {
    }

    #[Route(
        '/parametres/confidentialite',
        name: 'app_analytics_privacy_settings',
        host: 'utilisateur.{marque}.fr',
        requirements: ['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'],
        methods: ['GET'],
    )]
    public function show(Request $request): Response
    {
        return $this->render('renaissance/parametres/confidentialite.html.twig', [
            'consent_state' => $this->cookieHelper->read($request),
        ]);
    }

    #[Route(
        '/parametres/confidentialite/toggle',
        name: 'app_analytics_privacy_settings_toggle',
        host: 'utilisateur.{marque}.fr',
        requirements: ['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'],
        methods: ['POST'],
    )]
    public function toggle(Request $request): Response
    {
        $granted = '1' === $request->request->get('granted', '0');
        $source = $request->request->get('source', 'banner'); // 'banner' | 'settings'
        $cookie = $this->cookieHelper->write($granted);

        $event = $granted ? PostHogEventName::CONSENT_GRANTED : PostHogEventName::CONSENT_REFUSED;
        $user = $this->getUser();
        $this->service->captureServerSide($event, [
            'source' => $source,
            'consent_version' => '1',
        ], $user instanceof \App\Entity\Adherent ? $user : null);

        $response = new Response('', 204);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
