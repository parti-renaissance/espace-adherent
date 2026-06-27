<?php

declare(strict_types=1);

namespace Tests\App\Unit\Sentry\Webhook\Routing;

use App\Sentry\Webhook\Routing\SentryEventRouter;
use App\Sentry\Webhook\SentryEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
class SentryEventRouterTest extends TestCase
{
    private const MOBILE_PROJECT = '4511585504067664';
    private const BACKEND_PROJECT = '4511585443381328';

    private SentryEventRouter $router;

    protected function setUp(): void
    {
        // Slack is limited to the backend-php categories; ClickUp covers all six.
        $this->router = new SentryEventRouter(
            [
                'mobile' => [
                    'staging' => ['clickup' => 'CU_MOBILE_STG'],
                    'production' => ['clickup' => 'CU_MOBILE_PRD'],
                ],
                'backend-php' => [
                    'staging' => ['slack' => 'SL_BE_PHP_STG', 'clickup' => 'CU_BE_PHP_STG'],
                    'production' => ['slack' => 'SL_BE_PHP_PRD', 'clickup' => 'CU_BE_PHP_PRD'],
                ],
                'backend-js' => [
                    'staging' => ['clickup' => 'CU_BE_JS_STG'],
                    'production' => ['clickup' => 'CU_BE_JS_PRD'],
                ],
            ],
            self::MOBILE_PROJECT,
            self::BACKEND_PROJECT,
        );
    }

    /**
     * @return iterable<string, array{string, ?string, string, string, ?string, string}>
     */
    public static function provideSixCategories(): iterable
    {
        yield 'mobile staging' => [self::MOBILE_PROJECT, 'javascript', 'staging', 'mobile', null, 'CU_MOBILE_STG'];
        yield 'mobile production' => [self::MOBILE_PROJECT, 'javascript', 'production', 'mobile', null, 'CU_MOBILE_PRD'];
        yield 'backend php staging' => [self::BACKEND_PROJECT, 'php', 'staging', 'backend-php', 'SL_BE_PHP_STG', 'CU_BE_PHP_STG'];
        yield 'backend php production' => [self::BACKEND_PROJECT, 'php', 'production', 'backend-php', 'SL_BE_PHP_PRD', 'CU_BE_PHP_PRD'];
        yield 'backend js staging' => [self::BACKEND_PROJECT, 'javascript', 'staging', 'backend-js', null, 'CU_BE_JS_STG'];
        yield 'backend js production' => [self::BACKEND_PROJECT, 'javascript', 'production', 'backend-js', null, 'CU_BE_JS_PRD'];
    }

    #[DataProvider('provideSixCategories')]
    public function testRouteResolvesEachCategory(
        string $projectId,
        ?string $platform,
        string $environment,
        string $expectedCategory,
        ?string $expectedSlackChannelId,
        string $expectedClickUpChannelId,
    ): void {
        $decision = $this->router->route($this->event($projectId, $platform, $environment));

        self::assertNotNull($decision);
        self::assertSame($expectedCategory, $decision->category);
        self::assertSame($expectedSlackChannelId, $decision->slackChannelId);
        self::assertSame($expectedClickUpChannelId, $decision->clickUpChannelId);
    }

    /**
     * @return iterable<string, array{string, ?string, ?string}>
     */
    public static function provideUnroutable(): iterable
    {
        yield 'unknown project' => ['999999', 'php', 'production'];
        yield 'backend non-whitelisted platform' => [self::BACKEND_PROJECT, 'cocoa', 'production'];
        yield 'missing environment' => [self::BACKEND_PROJECT, 'php', null];
        yield 'environment not in table' => [self::BACKEND_PROJECT, 'php', 'review'];
    }

    #[DataProvider('provideUnroutable')]
    public function testRouteReturnsNullWhenUnroutable(string $projectId, ?string $platform, ?string $environment): void
    {
        self::assertNull($this->router->route($this->event($projectId, $platform, $environment)));
    }

    public function testRouteReturnsNullWhenCategoryHasNoConfiguredChannel(): void
    {
        $router = new SentryEventRouter(
            ['backend-php' => ['production' => ['slack' => '', 'clickup' => '']]],
            self::MOBILE_PROJECT,
            self::BACKEND_PROJECT,
        );

        self::assertNull($router->route($this->event(self::BACKEND_PROJECT, 'php', 'production')));
    }

    private function event(string $projectId, ?string $platform, ?string $environment): SentryEvent
    {
        return new SentryEvent($projectId, $platform, $environment, 'issue-1', 'Boom', null, 'error', null);
    }
}
