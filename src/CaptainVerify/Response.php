<?php

namespace App\CaptainVerify;

class Response
{
    public ?string $result = null;
    public ?string $details = null;
    public ?string $message = null;
    public bool $success = false;
    public readonly string $date;

    public function __construct()
    {
        $this->date = (new \DateTime())->format('Y-m-d H:i:s');
    }

    public function isValid(): bool
    {
        return $this->isSuccess() && 'valid' === $this->result;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
