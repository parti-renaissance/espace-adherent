<?php

namespace App\HtmlPurifier;

class HtmlPurifierProxy extends \HTMLPurifier
{
    public function __construct(private readonly \HTMLPurifier $purifier)
    {
    }

    public function purify($html, $config = null): ?string
    {
        return $this->purifier->purify($html, $config);
    }
}
