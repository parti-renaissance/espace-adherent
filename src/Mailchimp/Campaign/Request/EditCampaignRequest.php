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

        return [
            'type' => $this->type,
            'settings' => $settings,
        ];
    }
}
