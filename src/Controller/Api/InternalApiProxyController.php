<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\InternalApiApplication;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
#[Route(path: '/v3/internal/{uuid}/{path}', name: 'api_internal_api_application', requirements: ['path' => '.+', 'uuid' => '%pattern_uuid%'])]
class InternalApiProxyController extends AbstractController
{
    use ScopeTrait;

    // here, the forbidden headers in lower case
    private const FORBIDDEN_HEADERS = [
        'host',
        'x-user-uuid',
        'content-length',
    ];

    public function __construct(GeneralScopeGenerator $generalScopeGenerator)
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    public function __invoke(
        Request $request,
        InternalApiApplication $internalApiApplication,
        string $path,
        HttpClientInterface $internalApiProxyClient,
        SerializerInterface $serializer,
    ): Response {
        /** @var Adherent $user */
        $user = $this->getUser();

        $subRequestOption = [
            'headers' => array_merge($this->getFilteredRequestHeaders($request), [
                'X-User-UUID' => $user->getUuid()->toString(),
            ]),
            'query' => $request->query->all(),
        ];

        if ($internalApiApplication->isScopeRequired()) {
            if (!$scopeCode = $request->query->get('scope')) {
                throw new BadRequestHttpException('No scope provided.');
            }

            $subRequestOption['headers']['X-Scope'] = base64_encode(
                $serializer->serialize($this->getScope($scopeCode, $user), 'json', ['groups' => ['scope']])
            );
        }

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            // Body
            $subRequestOption['body'] = $request->getContent();
        }

        $response = $internalApiProxyClient->request(
            $request->getMethod(),
            $internalApiApplication->getHostname().'/'.$path,
            $subRequestOption
        );

        return new Response(
            $response->getContent(false),
            $response->getStatusCode(),
            array_intersect_key($response->getHeaders(false), array_fill_keys(['content-type', 'content-encoding'], true))
        );
    }

    private function getFilteredRequestHeaders(Request $request): array
    {
        return array_filter($request->headers->all(), function ($header) {
            return !\in_array(strtolower($header), self::FORBIDDEN_HEADERS);
        }, \ARRAY_FILTER_USE_KEY);
    }
}
