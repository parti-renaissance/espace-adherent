<?php

namespace App\Controller\Api\AppLink;

use App\Controller\Renaissance\Adhesion\AdhesionController;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[Route(path: '/v3/app-link/{key}', name: 'api_app_link_authenticated', methods: ['GET'])]
#[Security("is_granted('ROLE_USER')")]
class AuthenticatedAppLinkController extends AbstractController
{
    private const KEYS_TO_ROUTES = [
        'adhesion' => AdhesionController::ROUTE_NAME,
        'donation' => 'app_donation_index',
        'contribution' => 'app_renaissance_contribution_fill_revenue',
    ];

    public function __invoke(
        Request $request,
        LoginLinkHandlerInterface $loginLinkHandler,
        string $key
    ): JsonResponse {
        /** @var Adherent $user */
        $user = $this->getUser();

        if (!\array_key_exists($key, self::KEYS_TO_ROUTES)) {
            throw new BadRequestHttpException(\sprintf('No route found for key "%s".', $key));
        }

        return $this->json(
            $loginLinkHandler->createLoginLink(
                $user,
                $request,
                null,
                $this->generateUrl(self::KEYS_TO_ROUTES[$key])
            ),
        );
    }
}
