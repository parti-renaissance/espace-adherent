<?php

declare(strict_types=1);

namespace App\ErrorRenderer;

use App\OAuth\App\AuthAppUrlManager;
use Symfony\Bridge\Twig\ErrorRenderer\TwigErrorRenderer as BaseTwigErrorRenderer;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class TwigErrorRenderer implements ErrorRendererInterface
{
    private Environment $twig;
    private RequestStack $requestStack;
    private AuthAppUrlManager $appUrlManager;
    private BaseTwigErrorRenderer $fallbackErrorRenderer;
    private $debug;

    public function __construct(
        Environment $twig,
        RequestStack $requestStack,
        AuthAppUrlManager $appUrlManager,
        BaseTwigErrorRenderer $fallbackErrorRenderer,
        callable $debug,
    ) {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->appUrlManager = $appUrlManager;
        $this->fallbackErrorRenderer = $fallbackErrorRenderer;
        $this->debug = $debug;
    }

    public function render(\Throwable $exception): FlattenException
    {
        $exception = $this->fallbackErrorRenderer->render($exception);

        if (\is_bool($this->debug) ? $this->debug : ($this->debug)($exception)) {
            return $exception;
        }

        $request = $this->requestStack->getCurrentRequest();

        $appCode = $this->appUrlManager->getAppCodeFromRequest($request);

        if (!$appCode || !$template = $this->findTemplate($exception->getStatusCode(), $appCode)) {
            return $exception;
        }

        return $exception->setAsString($this->twig->render($template, [
            'exception' => $exception,
            'status_code' => $exception->getStatusCode(),
            'status_text' => $exception->getStatusText(),
        ]));
    }

    private function findTemplate(int $statusCode, string $appCode): ?string
    {
        $template = \sprintf('@Twig/Exception/%s/error%s.html.twig', $appCode, $statusCode);
        if (($loader = $this->twig->getLoader())->exists($template)) {
            return $template;
        }

        $template = \sprintf('@Twig/Exception/%s/error.html.twig', $appCode);
        if ($loader->exists($template)) {
            return $template;
        }

        return null;
    }
}
