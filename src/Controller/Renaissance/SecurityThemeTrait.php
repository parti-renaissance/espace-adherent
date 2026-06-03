<?php

declare(strict_types=1);

namespace App\Controller\Renaissance;

use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

trait SecurityThemeTrait
{
    private Environment $twig;

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    /**
     * Renders a security template through the app-specific theme folder when one exists
     * (e.g. security/campaign/<template>), falling back to the default renaissance theme
     * otherwise. A null app code (unknown host) uses the default too.
     */
    private function renderSecurityTheme(Request $request, AuthAppUrlManager $appUrlManager, string $template, array $parameters = []): Response
    {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $themedTemplate = \sprintf('security/%s/%s', $appCode, $template);

        return $this->render(
            $appCode && $this->twig->getLoader()->exists($themedTemplate) ? $themedTemplate : 'security/renaissance/'.$template,
            $parameters,
        );
    }
}
