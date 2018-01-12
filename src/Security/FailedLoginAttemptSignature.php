<?php

namespace AppBundle\Security;

class FailedLoginAttemptSignature
{
    private $args;

    public function __construct(string ...$args)
    {
        $this->args = $args;
    }

    public function __invoke(): string
    {
        return sha1(\GuzzleHttp\json_encode(sort($this->args)));
    }
}
