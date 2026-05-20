<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailer\Template;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Mailer\Template\Manager;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testGetTemplateContentWithNullContentReturnsEmptyString(): void
    {
        $template = new TransactionalEmailTemplate();
        // No setContent() call: content stays null (a template created without content).

        self::assertSame('', $this->manager()->getTemplateContent($template));
    }

    public function testGetTemplateContentWithNullContentAndParentDoesNotThrow(): void
    {
        $parent = new TransactionalEmailTemplate();
        $parent->setContent('<html><body><table class="template-email-block"><tr><td><div>PLACEHOLDER</div></td></tr></table></body></html>');

        $template = new TransactionalEmailTemplate();
        $template->parent = $parent;
        // Child has no content: must fall back to an empty body, not crash preg_*.

        $content = $this->manager()->getTemplateContent($template);

        self::assertStringContainsString('template-email-block', $content);
        self::assertStringNotContainsString('PLACEHOLDER', $content);
    }

    public function testGetTemplateContentWithContentInjectsLangAttribute(): void
    {
        $template = new TransactionalEmailTemplate();
        $template->setContent('<html><body><p>Hello</p></body></html>');

        $content = $this->manager()->getTemplateContent($template);

        self::assertStringContainsString('<p>Hello</p>', $content);
        self::assertStringContainsString('lang="fr"', $content);
    }

    private function manager(): Manager
    {
        // getTemplateContent() does not touch the repository.
        return new Manager($this->createMock(TransactionalEmailTemplateRepository::class));
    }
}
