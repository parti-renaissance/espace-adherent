<?php

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

interface RedirectToInterface
{
    public function handle(GetResponseForExceptionEvent $event, string $requestUri, string $redirectCode): bool;
}
