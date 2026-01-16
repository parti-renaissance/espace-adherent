<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Controller\Renaissance\Adhesion\AdhesionController;
use App\Entity\Adherent;
use App\OAuth\Model\Scope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
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
        'formation' => ['route_name' => 'formation_auth_start_redirect', 'allowed_options' => ['state']],
    ];

    private const ALLOWED_QUERY_PARAMS = [
        'utm_source',
        'utm_campaign',
    ];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly string $adminRenaissanceHost,
    ) {
    }

    public function __invoke(Request $request, string $key, #[CurrentUser] Adherent $user): JsonResponse
    {
        if (!\array_key_exists($key, self::KEYS_TO_ROUTES)) {
            throw new BadRequestHttpException(\sprintf('No route found for key "%s".', $key));
        }

        $targetPath = $this->prepareTargetPath($key, $request);

        if ($this->isGranted(Scope::generateRole(Scope::IMPERSONATOR))) {
            return $this->json([
                'url' => \sprintf('//%s%s', $this->adminRenaissanceHost, $targetPath),
                'expires_at' => null,
            ]);
        }

        return $this->json($this->loginLinkHandler->createLoginLink($user, $request, targetPath: $targetPath));
    }

    private function prepareTargetPath(string $key, Request $request): string
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

        foreach (self::ALLOWED_QUERY_PARAMS as $queryParam) {
            if ($request->query->has($queryParam)) {
                $parameters[$queryParam] = $request->query->get($queryParam);
            }
        }

        $url = $this->urlGenerator->generate($routeName, $parameters);

        $urlParts = parse_url($url);
        $path = $urlParts['path'] ?? '/';
        $query = isset($urlParts['query']) ? '?'.$urlParts['query'] : '';
        $fragment = isset($urlParts['fragment']) ? '#'.$urlParts['fragment'] : '';

        return $path.$query.$fragment;
    }
}
