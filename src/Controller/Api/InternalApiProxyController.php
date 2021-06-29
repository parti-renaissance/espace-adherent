<?php

namespace App\Controller\Api;

use App\Entity\InternalApiApplication;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/v3/internal/{uuid}/{path}", name="api_internal_api_application", requirements={"path": ".+", "uuid": "%pattern_uuid%"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class InternalApiProxyController extends AbstractController
{
    // here, the forbidden headers in lower case
    private const FORBIDDEN_HEADERS = [
        'host',
        'x-user-uuid',
        'content-length',
    ];

    /** @var GeneralScopeGenerator */
    private $generalScopeGenerator;

    public function __construct(GeneralScopeGenerator $generalScopeGenerator)
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    public function __invoke(
        Request $request,
        InternalApiApplication $internalApiApplication,
        string $path,
        HttpClientInterface $internalApiProxyClient,
        UserInterface $user,
        SerializerInterface $serializer
    ): Response {
        $subRequestOption = [
            'headers' => array_merge($this->getFilteredRequestHeaders($request), [
                'X-User-UUID' => $user->getUuid()->toString(),
            ]),
        ];

        if ($internalApiApplication->isScopeRequired()) {
            if (!$scopeCode = $request->query->get('scope')) {
                throw new BadRequestHttpException('No scope provided.');
            }

            $data = $this->getScope($scopeCode, $user);
            if (!$data) {
                throw $this->createAccessDeniedException('User has no required scope.');
            }

            $subRequestOption['headers']['X-Scope'] = base64_encode(
                $serializer->serialize($data, 'json', ['groups' => ['scope']])
            );
        }

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            //Body
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
            $response->getHeaders(false)
        );
    }

    private function getFilteredRequestHeaders(Request $request): array
    {
        return array_filter($request->headers->all(), function ($header) {
            return !\in_array(strtolower($header), self::FORBIDDEN_HEADERS);
        }, \ARRAY_FILTER_USE_KEY);
    }

    private function getScope(string $scopeCode, UserInterface $adherent): ?Scope
    {
        try {
            $generator = $this->generalScopeGenerator->getGenerator($scopeCode);
            if (!$generator->supports($adherent)) {
                return null;
            }

            return $generator->generate($adherent);
        } catch (InvalidScopeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
