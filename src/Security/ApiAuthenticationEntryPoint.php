<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly AuthenticationEntryPointInterface $decorated)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (
            \in_array('application/json', $request->getAcceptableContentTypes())
            || str_starts_with($request->getPathInfo(), '/api')
        ) {
            return new JsonResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return $this->decorated->start($request, $authException);
    }
}
