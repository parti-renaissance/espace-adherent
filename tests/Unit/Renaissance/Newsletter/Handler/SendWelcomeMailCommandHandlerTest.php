<?php

declare(strict_types=1);

namespace Tests\App\Unit\Renaissance\Newsletter\Handler;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Entity\Renaissance\NewsletterSource;
use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Message;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use App\Renaissance\Newsletter\Handler\SendWelcomeMailCommandHandler;
use App\Renaissance\Newsletter\SubscriptionRequest;
use App\Repository\Renaissance\NewsletterSourceRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendWelcomeMailCommandHandlerTest extends TestCase
{
    public function testSendWithConfiguredSourceTemplateUsesIt(): void
    {
        $template = new TransactionalEmailTemplate();
        $source = $this->createSource('site_eu', $template);

        $repository = $this->createMock(NewsletterSourceRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneByCode')
            ->with('site_eu')
            ->willReturn($source)
        ;

        $sent = $this->handle('site_eu', $repository);

        self::assertSame($template, $sent->getTemplateObject());
    }

    public function testSendWithSourceWithoutTemplateFallsBackToNull(): void
    {
        $source = $this->createSource('site_eu', null);

        $repository = $this->createMock(NewsletterSourceRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneByCode')
            ->with('site_eu')
            ->willReturn($source)
        ;

        $sent = $this->handle('site_eu', $repository);

        self::assertNull($sent->getTemplateObject());
    }

    public function testSendWithNullSourceDoesNotQueryRepository(): void
    {
        $repository = $this->createMock(NewsletterSourceRepository::class);
        $repository
            ->expects(self::never())
            ->method('findOneByCode')
        ;

        $sent = $this->handle(null, $repository);

        self::assertNull($sent->getTemplateObject());
    }

    /**
     * Runs the handler and returns the Message actually passed to the mailer.
     */
    private function handle(?string $source, NewsletterSourceRepository $repository): Message
    {
        $sent = null;

        $mailer = $this->createMock(MailerService::class);
        $mailer
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::callback(static function (Message $message) use (&$sent): bool {
                $sent = $message;

                return true;
            }))
            ->willReturn(true)
        ;

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('app_renaissance_newsletter_confirm', self::anything(), UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.test/confirm')
        ;

        $handler = new SendWelcomeMailCommandHandler($mailer, $urlGenerator, $repository);
        $handler(new SendWelcomeMailCommand($this->createSubscription($source)));

        self::assertInstanceOf(Message::class, $sent);

        return $sent;
    }

    private function createSubscription(?string $source): NewsletterSubscription
    {
        $request = new SubscriptionRequest();
        $request->email = 'john@example.test';
        $request->source = $source;

        return NewsletterSubscription::create($request);
    }

    private function createSource(string $code, ?TransactionalEmailTemplate $template): NewsletterSource
    {
        $source = new NewsletterSource();
        $source->code = $code;
        $source->label = 'Source de test';
        $source->confirmationEmailTemplate = $template;

        return $source;
    }
}
