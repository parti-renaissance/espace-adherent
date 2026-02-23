<?php

declare(strict_types=1);

namespace Tests\App\Chatbot;

use App\Chatbot\ChatbotManager;
use App\Entity\Adherent;
use App\Entity\Chatbot\Thread;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\Role;

#[Group('functional')]
#[Group('chatbot')]
class ChatbotManagerTest extends TestCase
{
    private ChatbotManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ChatbotManager(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ThreadRepository::class),
        );
    }

    public function testBuildContextMessageBagReturnsAllMessagesWhenUnderLimit(): void
    {
        $thread = $this->createThreadWithMessages(3);

        $bag = $this->manager->buildContextMessageBag($thread);
        $messages = array_values(iterator_to_array($bag));

        self::assertCount(3, $messages);
        self::assertSame(Role::User, $messages[0]->getRole());
        self::assertSame('U1', $messages[0]->getContent()[0]->getText());
        self::assertSame(Role::Assistant, $messages[1]->getRole());
        self::assertSame('A1', $messages[1]->getContent());
        self::assertSame(Role::User, $messages[2]->getRole());
        self::assertSame('U2', $messages[2]->getContent()[0]->getText());
    }

    public function testBuildContextMessageBagTruncatesToLastFifteenMessages(): void
    {
        $thread = $this->createThreadWithMessages(20);

        $bag = $this->manager->buildContextMessageBag($thread);
        $messages = array_values(iterator_to_array($bag));

        self::assertCount(14, $messages);
        self::assertSame(Role::User, $messages[0]->getRole());
        self::assertSame('U4', $messages[0]->getContent()[0]->getText());
        self::assertSame(Role::Assistant, $messages[13]->getRole());
        self::assertSame('A10', $messages[13]->getContent());
    }

    public function testBuildContextMessageBagDoesNotStartWithOrphanAssistantMessage(): void
    {
        $thread = $this->createThreadWithMessages(16);

        $bag = $this->manager->buildContextMessageBag($thread);
        $messages = array_values(iterator_to_array($bag));

        self::assertCount(14, $messages);
        self::assertSame(Role::User, $messages[0]->getRole());
        self::assertSame('U2', $messages[0]->getContent()[0]->getText());
        self::assertSame(Role::Assistant, $messages[13]->getRole());
        self::assertSame('A8', $messages[13]->getContent());
    }

    private function createThreadWithMessages(int $count): Thread
    {
        $adherent = $this->createMock(Adherent::class);
        $thread = new Thread($adherent);
        $base = new \DateTimeImmutable('-1 hour');

        for ($i = 1; $i <= $count; ++$i) {
            $date = $base->modify("+{$i} minutes");
            if (1 === $i % 2) {
                $thread->addUserMessage('U'.(int) (($i + 1) / 2), $date);
            } else {
                $thread->addAssistantMessage('A'.(int) ($i / 2), $date);
            }
        }

        return $thread;
    }
}
