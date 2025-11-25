<?php

namespace App\Controller\Api\Vox;

use App\Controller\Renaissance\Adhesion\AdhesionController;
use App\Entity\Adherent;
use App\OAuth\Model\Scope;
use App\Repository\OAuth\ClientRepository;
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
        private readonly string $adminRenaissanceHost,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function __invoke(Request $request, string $userVoxHost, LoginLinkHandlerInterface $loginLinkHandler, string $key, #[CurrentUser] Adherent $user, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        if (!\array_key_exists($key, self::KEYS_TO_ROUTES)) {
            throw new BadRequestHttpException(\sprintf('No route found for key "%s".', $key));
        }

        if ('cadre' === $key && $this->isGranted(Scope::generateRole(Scope::IMPERSONATOR))) {
            return $this->json($this->getCadreLink());
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

        foreach (self::ALLOWED_QUERY_PARAMS as $queryParam) {
            if ($request->query->has($queryParam)) {
                $parameters[$queryParam] = $request->query->get($queryParam);
            }
        }

        return [$routeName, $parameters];
    }

    private function getCadreLink(): array
    {
        $client = $this->clientRepository->getCadreClient();

        return [
            'url' => $this->generateUrl('app_front_oauth_authorize', [
                'app_domain' => $this->adminRenaissanceHost,
                'response_type' => 'code',
                'client_id' => $client->getUuid()->toString(),
                'redirect_uri' => $client->getRedirectUris()[0],
                'scope' => implode(' ', $client->getSupportedScopes()),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('PT10M')),
        ];
    }
}
