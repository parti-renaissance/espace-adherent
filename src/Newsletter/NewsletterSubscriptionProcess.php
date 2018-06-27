<?php

namespace AppBundle\Newsletter;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NewsletterSubscriptionProcess
{
    private const SUCCESS_REDIRECT_PATH = 'newsletter.success_redirect_path';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function init(?string $path): void
    {
        $this->session->set(self::SUCCESS_REDIRECT_PATH, $path);
    }

    public function getSuccessRedirectPath(): ?string
    {
        return $this->session->get(self::SUCCESS_REDIRECT_PATH);
    }

    public function terminate(): void
    {
        $this->session->remove(self::SUCCESS_REDIRECT_PATH);
    }
}
