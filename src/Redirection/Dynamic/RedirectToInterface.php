<?php

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

interface RedirectToInterface
{
    public function handle(ExceptionEvent $event, string $requestUri, string $redirectCode): bool;
}
