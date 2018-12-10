<?php

namespace AppBundle\Mailchimp\Synchronisation\Request;

interface MemberRequestInterface extends RequestInterface
{
    public function getMemberIdentifier(): string;
}
