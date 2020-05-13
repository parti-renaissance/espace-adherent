<?php

namespace App\Mailchimp\Campaign\Request;

use App\Mailchimp\RequestInterface;

class EditCampaignContentRequest implements RequestInterface
{
    private const SECTION_CONTENT_NAME = 'content';

    private $templateId;
    private $content;
    private $sections = [];

    public function __construct(int $templateId, string $content)
    {
        $this->templateId = $templateId;
        $this->content = $content;
    }

    public function addSection(string $key, string $value): self
    {
        $this->sections[$key] = $value;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'template' => [
                'id' => $this->templateId,
                'sections' => array_merge([self::SECTION_CONTENT_NAME => $this->content], $this->sections),
            ],
        ];
    }
}
