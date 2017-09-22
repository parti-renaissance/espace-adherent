<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Model\EmailTemplate;

interface EmailTemplateClientInterface
{
    public function synchronize(EmailTemplate $template): void;
}
