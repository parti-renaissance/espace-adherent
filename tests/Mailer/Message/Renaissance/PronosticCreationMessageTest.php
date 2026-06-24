<?php

declare(strict_types=1);

namespace Tests\App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Mailer\Message\Renaissance\PronosticCreationMessage;
use PHPUnit\Framework\TestCase;

class PronosticCreationMessageTest extends TestCase
{
    private Pronostic $pronostic;

    protected function setUp(): void
    {
        $this->pronostic = new Pronostic();
        $this->pronostic->title = 'France - Sénégal';
        $this->pronostic->team1 = 'France';
        $this->pronostic->team2 = 'Sénégal';
        $this->pronostic->matchAt = new \DateTimeImmutable('2026-07-01 20:00:00');
    }

    private function makeAdherent(string $email, string $firstName): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getEmailAddress')->willReturn($email);
        $adherent->method('getFullName')->willReturn($firstName.' Test');
        $adherent->method('getFirstName')->willReturn($firstName);

        return $adherent;
    }

    public function testSubjectContainsTitle(): void
    {
        $message = PronosticCreationMessage::create($this->pronostic, [$this->makeAdherent('a@b.com', 'Jean')]);

        self::assertSame('Nouveau pronostic : France - Sénégal', $message->getSubject());
    }

    public function testVarsContainPronosticData(): void
    {
        $message = PronosticCreationMessage::create($this->pronostic, [$this->makeAdherent('a@b.com', 'Jean')]);

        $vars = $message->getVars();
        self::assertSame('France - Sénégal', $vars['pronostic_title']);
        self::assertSame('France', $vars['team_1']);
        self::assertSame('Sénégal', $vars['team_2']);
        self::assertArrayHasKey('match_date', $vars);
        self::assertArrayHasKey('match_hour', $vars);
    }

    public function testMultipleRecipientsAreAdded(): void
    {
        $message = PronosticCreationMessage::create($this->pronostic, [
            $this->makeAdherent('a@b.com', 'Jean'),
            $this->makeAdherent('c@d.com', 'Marie'),
        ]);

        self::assertCount(2, $message->getRecipients());
    }

    public function testThrowsWithEmptyRecipients(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PronosticCreationMessage::create($this->pronostic, []);
    }
}
