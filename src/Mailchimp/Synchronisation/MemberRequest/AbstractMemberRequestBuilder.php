<?php

namespace AppBundle\Mailchimp\Synchronisation\MemberRequest;

use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

abstract class AbstractMemberRequestBuilder
{
    private $email;
    private $isSubscribeRequest = true;

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setIsSubscribeRequest(bool $isSubscribeRequest): self
    {
        $this->isSubscribeRequest = $isSubscribeRequest;

        return $this;
    }

    public function build(string $memberIdentifier): MemberRequest
    {
        $request = new MemberRequest($memberIdentifier);

        if ($this->email) {
            $request->setEmailAddress($this->email);
        }

        if (false === $this->isSubscribeRequest) {
            $request->setUnsubscriptionRequest();
        }

        $request->setMergeFields($this->buildMergeFields());

        return $request;
    }

    abstract protected function buildMergeFields(): array;
}
