<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Rendering;

use App\AdherentMessage\Variable\Dictionary;
use App\Ses\Rendering\SesRecipient;
use App\Ses\Rendering\SesRecipientContextFactory;
use App\ValueObject\Genders;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SesRecipientContextFactoryTest extends TestCase
{
    #[DataProvider('provideSalutations')]
    public function testSalutationFollowsGenderAndIsEmptyWhenGenderAbsent(?string $gender, string $expected): void
    {
        $context = new SesRecipientContextFactory()->create(new SesRecipient('a@b.fr', 'uuid-1', 'Jean', 'Dupont', $gender, '12345'));

        self::assertSame($expected, $context[$this->code(Dictionary::SALUTATION)]);
    }

    public static function provideSalutations(): iterable
    {
        yield 'female' => [Genders::FEMALE, 'Chère Jean'];
        yield 'male' => [Genders::MALE, 'Cher Jean'];
        yield 'null is empty (Mailchimp parity)' => [null, ''];
        yield 'other is empty' => [Genders::OTHER, ''];
        yield 'unknown is empty' => [Genders::UNKNOWN, ''];
    }

    public function testValuesAreHtmlEscaped(): void
    {
        $context = new SesRecipientContextFactory()->create(
            new SesRecipient('a@b.fr', 'uuid-1', '<script>alert(1)</script>', '<b>D</b>', Genders::FEMALE, '<i>9</i>')
        );

        self::assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $context[$this->code(Dictionary::FIRST_NAME)]);
        self::assertSame('&lt;b&gt;D&lt;/b&gt;', $context[$this->code(Dictionary::LAST_NAME)]);
        self::assertSame('&lt;i&gt;9&lt;/i&gt;', $context[$this->code(Dictionary::PUBLIC_ID)]);
        self::assertSame('Chère &lt;script&gt;alert(1)&lt;/script&gt;', $context[$this->code(Dictionary::SALUTATION)]);
    }

    private function code(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}
