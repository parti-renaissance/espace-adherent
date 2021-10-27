<?php

namespace App\SmsCampaign\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class CatchOvhSmsWebhookCallCommand implements AsynchronousMessageInterface
{
    public $method;
    public $headers;
    public $query;
    public $request;
    public $content;

    public function __construct($method, $headers, $query, $request, $content)
    {
        $this->method = $method;
        $this->headers = $headers;
        $this->query = $query;
        $this->request = $request;
        $this->content = $content;
    }
}
