<?php

namespace AppBundle\Mailchimp\Synchronisation\Request;

use AppBundle\Mailchimp\RequestInterface;

interface MemberRequestInterface extends RequestInterface
{
    public function getMemberIdentifier(): string;
}
