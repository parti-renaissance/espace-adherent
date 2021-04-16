<?php

namespace App\Controller\Api;

use App\Entity\InternalApiApplication;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/v3/internal/{uuid}/{path}", name="api_internal_api_application", requirements={"path": ".+"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class InternalApiProxyController extends AbstractController
{
    public function __invoke(
        Request $request,
        InternalApiApplication $internalApiApplication,
        string $path,
        HttpClientInterface $internalApiProxyClient,
        UserInterface $user
    ): Response {
        $subRequestOption = [
            'headers' => [
                'X-User-UUID' => $user->getUuid()->toString(),
            ],
        ];

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            // Headers
            $subRequestOption['headers']['Content-Type'] = $request->headers->get('Content-Type');
            $subRequestOption['headers']['Authorization'] = $request->headers->get('Authorization');

            //Body
            $subRequestOption['body'] = $request->getContent();
        }

        $response = $internalApiProxyClient->request(
            $request->getMethod(),
            $internalApiApplication->getHostname().'/'.$path,
            $subRequestOption
        );

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
