<?php

declare(strict_types=1);

namespace Tests\App\Entity\Email;

use App\Entity\Email\EmailSender;
use App\Entity\Email\TransactionalEmailTemplate;
use PHPUnit\Framework\TestCase;

class TransactionalEmailTemplateTest extends TestCase
{
    public function testEffectiveSenderReturnsOwnSenderWhenSet(): void
    {
        $sender = $this->createSender('Child', 'child@example.org');
        $template = new TransactionalEmailTemplate();
        $template->sender = $sender;

        self::assertSame($sender, $template->getEffectiveSender());
    }

    public function testEffectiveSenderInheritsFromParentWhenChildHasNone(): void
    {
        $parentSender = $this->createSender('Parent', 'parent@example.org');
        $parent = new TransactionalEmailTemplate();
        $parent->sender = $parentSender;

        $child = new TransactionalEmailTemplate();
        $child->parent = $parent;

        self::assertSame($parentSender, $child->getEffectiveSender());
    }

    public function testEffectiveSenderPrefersChildOverParent(): void
    {
        $parent = new TransactionalEmailTemplate();
        $parent->sender = $this->createSender('Parent', 'parent@example.org');

        $childSender = $this->createSender('Child', 'child@example.org');
        $child = new TransactionalEmailTemplate();
        $child->parent = $parent;
        $child->sender = $childSender;

        self::assertSame($childSender, $child->getEffectiveSender());
    }

    public function testEffectiveSenderReturnsNullWhenNoneAnywhere(): void
    {
        $parent = new TransactionalEmailTemplate();
        $child = new TransactionalEmailTemplate();
        $child->parent = $parent;

        self::assertNull($child->getEffectiveSender());
    }

    private function createSender(string $name, string $email): EmailSender
    {
        $sender = new EmailSender();
        $sender->name = $name;
        $sender->email = $email;

        return $sender;
    }
}
