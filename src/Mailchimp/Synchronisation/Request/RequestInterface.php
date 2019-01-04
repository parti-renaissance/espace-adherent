<?php

namespace AppBundle\Mailchimp\Synchronisation\Request;

interface RequestInterface
{
    public function toArray(): array;
}
