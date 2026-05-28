<?php

declare(strict_types=1);

namespace Tests\App\Validator\Email;

use App\Validator\Email\EmailTypoValidation;
use App\Validator\Email\Reason\EmailTypoReason;
use Egulias\EmailValidator\EmailLexer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmailTypoValidationTest extends TestCase
{
    #[DataProvider('suggestionCases')]
    public function testSuggest(string $email, ?string $expectedSuggestion): void
    {
        $validator = new EmailTypoValidation();
        $isValid = $validator->isValid($email, new EmailLexer());

        if (null === $expectedSuggestion) {
            self::assertTrue($isValid, "Expected no suggestion for '$email', got one.");
            self::assertNull($validator->getError());

            return;
        }

        self::assertFalse($isValid, "Expected suggestion '$expectedSuggestion' for '$email', got valid.");
        $error = $validator->getError();
        self::assertNotNull($error);

        $reason = $error->reason();
        self::assertInstanceOf(EmailTypoReason::class, $reason);
        self::assertSame($expectedSuggestion, $reason->suggestion);
    }

    public static function suggestionCases(): \Iterator
    {
        // Stage 1: full-domain typo
        yield 'gmai.com → gmail.com' => ['user@gmai.com', 'user@gmail.com'];
        yield 'gmial.com → gmail.com' => ['user@gmial.com', 'user@gmail.com'];
        yield 'gmaill.com → gmail.com' => ['user@gmaill.com', 'user@gmail.com'];
        yield 'gmailc.om → gmail.com' => ['user@gmailc.om', 'user@gmail.com'];
        yield 'hotnail.fr → hotmail.fr' => ['user@hotnail.fr', 'user@hotmail.fr'];
        yield 'outlok.fr → outlook.fr' => ['user@outlok.fr', 'user@outlook.fr'];

        // Stage 1: extended-whitelist domains
        yield 'iclou.com → icloud.com' => ['user@iclou.com', 'user@icloud.com'];
        yield 'live.fre → live.fr' => ['user@live.fre', 'user@live.fr'];
        yield 'aoll.com → aol.com' => ['user@aoll.com', 'user@aol.com'];
        yield 'protonmial.com → protonmail.com' => ['user@protonmial.com', 'user@protonmail.com'];

        // Stage 2: TLD-only typo (label exact match)
        yield 'gmail.con → gmail.com' => ['user@gmail.con', 'user@gmail.com'];
        yield 'icloud.con → icloud.com' => ['user@icloud.con', 'user@icloud.com'];
        yield 'me.con → me.com' => ['user@me.con', 'user@me.com'];
        yield 'aol.con → aol.com' => ['user@aol.con', 'user@aol.com'];

        // Case normalisation
        yield 'USER@GMAI.COM → user@gmail.com' => ['USER@GMAI.COM', 'user@gmail.com'];

        // No suggestion: already perfect (exact match in whitelist)
        yield 'gmail.com → null' => ['user@gmail.com', null];
        yield 'gmail.fr → null (legitimate TLD on popular label)' => ['user@gmail.fr', null];
        yield 'live.fr → null' => ['user@live.fr', null];
        yield 'live.com → null' => ['user@live.com', null];
        yield 'icloud.com → null' => ['user@icloud.com', null];
        yield 'me.com → null' => ['user@me.com', null];
        yield 'protonmail.com → null (whitelisted)' => ['user@protonmail.com', null];

        // No suggestion: Codex false-positive prevention (TLD threshold = 1, label not popular)
        yield 'example.ai → null' => ['user@example.ai', null];
        yield 'example.dev → null' => ['user@example.dev', null];
        yield 'example.me → null' => ['user@example.me', null];
        yield 'example.info → null' => ['user@example.info', null];
        yield 'example.it → null' => ['user@example.it', null];
        yield 'example.ca → null' => ['user@example.ca', null];

        // No suggestion: malformed
        yield 'no @ sign' => ['foo', null];
        yield 'empty local' => ['@gmail.com', null];
        yield 'empty domain after @' => ['foo@', null];
        yield 'empty tld' => ['foo@bar.', null];
        yield 'empty label before tld' => ['foo@.com', null];

        // No suggestion: too long
        yield 'too long' => [str_repeat('a', 250).'@gmail.com', null];
    }
}
