<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\ProcurationRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProcurationRequestSession
{
    const SESSION_KEY = 'app_procuration_model';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function start(): void
    {
        $this->session->set(self::SESSION_KEY, new ProcurationRequest());
    }

    public function end(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    public function getCurrentModel(): ProcurationRequest
    {
        if (!$this->session->has(self::SESSION_KEY)) {
            $this->start();
        }

        return $this->session->get(self::SESSION_KEY);
    }
}
