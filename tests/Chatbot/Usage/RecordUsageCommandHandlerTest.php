<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Usage;

use App\Chatbot\Usage\RecordUsageCommand;
use App\Chatbot\Usage\RecordUsageCommandHandler;
use App\Entity\Chatbot\Message;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('functional')]
#[Group('chatbot')]
class RecordUsageCommandHandlerTest extends TestCase
{
    public function testInvokeStoresUsagePayloadOnMessageRaw(): void
    {
        $usage = [
            'prompt_tokens' => 9200,
            'completion_tokens' => 420,
            'total_tokens' => 9620,
            'prompt_tokens_details' => ['cached_tokens' => 7014],
            'by_model' => [
                'model-a' => [
                    'input_tokens' => 1292,
                    'output_tokens' => 269,
                    'cache_read_tokens' => 7014,
                    'cache_creation_tokens' => 336,
                ],
                'model-b' => [
                    'input_tokens' => 558,
                    'output_tokens' => 151,
                    'cache_read_tokens' => 0,
                    'cache_creation_tokens' => 0,
                ],
            ],
            'cost_usd' => 0.012588,
        ];

        $message = new Message();
        $entityManager = $this->createEntityManager($message, $flushed);

        $handler = new RecordUsageCommandHandler($entityManager);
        $handler(new RecordUsageCommand(7, $usage, 3450));

        self::assertSame([
            'usage' => $usage,
            'response_time_ms' => 3450,
        ], $message->raw);
        self::assertTrue($flushed);
    }

    public function testInvokeWithoutRawUsageStillStoresPayload(): void
    {
        $message = new Message();
        $entityManager = $this->createEntityManager($message, $flushed);

        $handler = new RecordUsageCommandHandler($entityManager);
        $handler(new RecordUsageCommand(7, null, null));

        self::assertSame([
            'usage' => null,
            'response_time_ms' => null,
        ], $message->raw);
        self::assertTrue($flushed);
    }

    public function testInvokeSkipsDeletedMessage(): void
    {
        $entityManager = $this->createEntityManager(null, $flushed);

        $handler = new RecordUsageCommandHandler($entityManager);
        $handler(new RecordUsageCommand(7, ['total_tokens' => 10], 100));

        self::assertFalse($flushed);
    }

    private function createEntityManager(?Message $message, ?bool &$flushed): EntityManagerInterface
    {
        $flushed = false;

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('find')->willReturn($message);
        $entityManager->method('flush')->willReturnCallback(static function () use (&$flushed): void {
            $flushed = true;
        });

        return $entityManager;
    }
}
