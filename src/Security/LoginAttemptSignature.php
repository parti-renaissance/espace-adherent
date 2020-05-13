<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

class LoginAttemptSignature
{
    private $args;
    private $login;
    private $ip;
    private $signature;

    private function __construct(string $login, string $ip)
    {
        $this->login = $login;
        $this->ip = $ip;

        $this->args = [
            $login, $ip,
        ];
    }

    public static function createFromRequest(Request $request): self
    {
        $login = mb_strtolower($request->request->get('_login_email'));

        return new self($login, $request->getClientIp());
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getSignature(): string
    {
        if (!$this->signature) {
            $this->signature = sha1(\GuzzleHttp\json_encode($this->args));
        }

        return $this->signature;
    }
}
