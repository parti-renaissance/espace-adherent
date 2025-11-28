<?php

declare(strict_types=1);

namespace App\Mailer\Command;

use App\Entity\Email\TransactionalEmailTemplate;

class UpdateTransactionalEmailTemplateCommand
{
    public function __construct(
        public ?string $identifier = null,
        public ?string $subject = null,
        public ?string $content = null,
        public ?string $jsonContent = null,
        public ?string $parent = null,
        public ?TransactionalEmailTemplate $parentObject = null,
    ) {
    }
}
