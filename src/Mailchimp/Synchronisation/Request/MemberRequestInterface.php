<?php

namespace App\Mailchimp\Synchronisation\Request;

use App\Mailchimp\RequestInterface;

interface MemberRequestInterface extends RequestInterface
{
    public function getMemberIdentifier(): string;
}
