<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\MemberRequest;

use App\Mailchimp\Synchronisation\Request\MemberRequest;

abstract class AbstractMemberRequestBuilder
{
    private $email;
    private $isSubscribeRequest = true;
    private $interests;

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

    public function setInterests(array $interests): self
    {
        $this->interests = $interests;

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

        if ($this->interests) {
            $request->setInterests($this->interests);
        }

        return $request;
    }

    abstract protected function buildMergeFields(): array;
}
