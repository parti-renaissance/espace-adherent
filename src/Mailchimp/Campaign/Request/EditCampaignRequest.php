<?php

namespace AppBundle\Mailchimp\Campaign\Request;

use AppBundle\Mailchimp\RequestInterface;

class EditCampaignRequest implements RequestInterface
{
    private $type = 'regular';
    private $folderId;
    private $templateId;
    private $subject;
    private $title;
    private $segmentOptions;
    private $fromName;
    private $replyTo;

    public function setFolderId(?string $id)
    {
        $this->folderId = $id;

        return $this;
    }

    public function setTemplateId(?int $id): self
    {
        $this->templateId = $id;

        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setFromName(?string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function isEmptySegmentConditions(): bool
    {
        return empty($this->segmentOptions['conditions']);
    }

    public function setSegmentOptions(array $segmentOptions): self
    {
        if (!empty($segmentOptions) && empty($segmentOptions['list_id'])) {
            throw new \InvalidArgumentException('You must instantiate a request object with Mailchimp List id for using the filters');
        }

        $this->segmentOptions = $segmentOptions;

        return $this;
    }

    public function toArray(): array
    {
        $settings = [];

        if ($this->folderId) {
            $settings['folder_id'] = $this->folderId;
        }

        if ($this->templateId) {
            $settings['template_id'] = $this->templateId;
        }

        if ($this->subject) {
            $settings['subject_line'] = $this->subject;
        }

        if ($this->title) {
            $settings['title'] = $this->title;
        }

        if ($this->replyTo) {
            $settings['reply_to'] = $this->replyTo;
        }

        if ($this->fromName) {
            $settings['from_name'] = $this->fromName;
        }

        if ($this->segmentOptions) {
            $recipients = ['recipients' => $this->segmentOptions];
        }

        return array_merge(
            [
                'type' => $this->type,
                'settings' => $settings,
            ],
            $recipients ?? []
        );
    }
}
