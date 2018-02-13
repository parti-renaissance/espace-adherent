<?php

namespace AppBundle\Mailer;

interface EmailTemplateClientInterface
{
    public function synchronize(string $template): void;
}
