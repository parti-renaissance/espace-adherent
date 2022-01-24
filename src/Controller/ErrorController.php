<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ErrorController extends AbstractController
{
    private HtmlErrorRenderer $errorRenderer;
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->errorRenderer = new HtmlErrorRenderer();
        $this->twig = $twig;
    }

    public function __invoke(\Throwable $exception, Request $request, string $jemengageAuthHost): Response
    {
        $exception = $this->errorRenderer->render($exception);

        $appCode = 'app';
        if ($jemengageAuthHost === $request->getHost()) {
            $appCode = 'jemengage';
        }

        return new Response(
            $this->twig->render(
                $this->findTemplate($exception->getStatusCode(), $appCode),
                [
                    'exception' => $exception,
                    'status_code' => $exception->getStatusCode(),
                    'status_text' => $exception->getStatusText(),
                ]
            ),
            $exception->getStatusCode(),
            $exception->getHeaders()
        );
    }

    private function findTemplate(int $statusCode, string $appCode): ?string
    {
        $template = sprintf('@Twig/Exception/%s/error%s.html.twig', $appCode, $statusCode);
        if ($this->templateExists($template)) {
            return $template;
        }

        $template = sprintf('@Twig/Exception/%s/error.html.twig', $appCode);
        if ($this->templateExists($template)) {
            return $template;
        }

        return null;
    }

    private function templateExists(string $template): bool
    {
        return $this->twig->getLoader()->exists($template);
    }
}
