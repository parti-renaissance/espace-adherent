<?php

declare(strict_types=1);

namespace App\Mailchimp\MailchimpSegment\Request;

use App\Mailchimp\RequestInterface;

class EditSegmentRequest implements RequestInterface
{
    private $name;
    private $options;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'options' => $this->options,
        ];
    }
}
