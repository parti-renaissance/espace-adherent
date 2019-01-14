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
    private $listId;
    private $conditions;

    public function __construct(string $listId = null)
    {
        $this->listId = $listId;
    }

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

    public function setConditions(array $conditions): self
    {
        if (!empty($conditions) && empty($this->listId)) {
            throw new \InvalidArgumentException(
                'You must instantiate a request object with Mailchimp List id for using the filters'
            );
        }

        $this->conditions = $conditions;

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

        if ($this->conditions) {
            $recipients = [
                'recipients' => [
                    'list_id' => $this->listId,
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => $this->conditions,
                    ],
                ],
            ];
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
