<?php

declare(strict_types=1);

namespace App\Redirection\Dynamic;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RedirectToPathHandler extends AbstractRedirectTo implements RedirectToInterface
{
    private $provider;

    public function __construct(RedirectionsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(ExceptionEvent $event, string $requestUri, int $redirectCode): bool
    {
        foreach ($this->provider->get(RedirectionsProvider::TO_PATH) as $pattern => $path) {
            if (!$this->hasPattern($pattern, $requestUri)) {
                continue;
            }

            if ($this->hasPattern('/articles/tribunes/', $requestUri)) {
                $path = str_replace($pattern, $path, $requestUri);
            }

            $event->setResponse(new RedirectResponse($path, $redirectCode));

            return true;
        }

        return false;
    }
}
