<?php

namespace App\Mailchimp\Synchronisation\Request;

class MemberTagsRequest implements MemberRequestInterface
{
    private $memberIdentifier;

    private $tags = [];

    public function __construct(string $memberIdentifier)
    {
        $this->memberIdentifier = $memberIdentifier;
    }

    public function getMemberIdentifier(): string
    {
        return $this->memberIdentifier;
    }

    public function addTag(string $tagName, bool $active = true): void
    {
        $this->tags[$tagName] = $active;
    }

    public function hasTags(): bool
    {
        return !empty($this->tags);
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->tags as $tagName => $active) {
            $result[] = [
                'name' => (string) $tagName,
                'status' => $active ? 'active' : 'inactive',
            ];
        }

        return ['tags' => $result];
    }
}
