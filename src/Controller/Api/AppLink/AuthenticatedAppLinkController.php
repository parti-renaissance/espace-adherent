<?php

namespace App\Controller\Api\AppLink;

use App\Controller\Renaissance\Adhesion\AdhesionController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/v3/app-link/{key}', name: 'api_app_link_authenticated', methods: ['GET'])]
class AuthenticatedAppLinkController extends AbstractController
{
    private const KEYS_TO_ROUTES = [
        'adhesion' => AdhesionController::ROUTE_NAME,
        'donation' => ['route_name' => 'app_donation_index', 'allowed_options' => ['duration']],
        'contribution' => 'app_renaissance_contribution_fill_revenue',
        'cadre' => ['route_name' => 'cadre_app_redirect', 'allowed_options' => ['state']],
    ];

    public function __invoke(Request $request, string $userVoxHost, LoginLinkHandlerInterface $loginLinkHandler, string $key, UserInterface $user, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        if (!\array_key_exists($key, self::KEYS_TO_ROUTES)) {
            throw new BadRequestHttpException(\sprintf('No route found for key "%s".', $key));
        }

        $context = $urlGenerator->getContext();
        $originalHost = $context->getHost();
        $context->setHost($userVoxHost);

        $targetPath = $urlGenerator->generate(...$this->prepareRouteParams($key, $request));

        $context->setHost($originalHost);

        return $this->json($loginLinkHandler->createLoginLink($user, $request, targetPath: $targetPath));
    }

    private function prepareRouteParams(string $key, Request $request): array
    {
        $route = $routeName = self::KEYS_TO_ROUTES[$key];
        $parameters = [];

        if (\is_array($route)) {
            $routeName = $route['route_name'];

            foreach ($route['allowed_options'] ?? [] as $optionKey) {
                if ($request->query->has($optionKey)) {
                    $parameters[$optionKey] = $request->query->get($optionKey);
                }
            }
        }

        return [$routeName, $parameters];
    }
}
