<?php

declare(strict_types=1);

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

interface RedirectToInterface
{
    public function handle(ExceptionEvent $event, string $requestUri, int $redirectCode): bool;
}
