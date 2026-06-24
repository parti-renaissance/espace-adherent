<?php

declare(strict_types=1);

namespace Tests\App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\Mailer\Message\Renaissance\PronosticResultMessage;
use PHPUnit\Framework\TestCase;

class PronosticResultMessageTest extends TestCase
{
    private Pronostic $pronostic;

    protected function setUp(): void
    {
        $this->pronostic = new Pronostic();
        $this->pronostic->title = 'France - Sénégal';
        $this->pronostic->team1 = 'France';
        $this->pronostic->team2 = 'Sénégal';
        $this->pronostic->resultTeam1Score = 2;
        $this->pronostic->resultTeam2Score = 1;
        $this->pronostic->resultPublishedAt = new \DateTimeImmutable();
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
        $participation = new PronosticParticipation($this->pronostic, $this->makeAdherent('a@b.com', 'Jean'), 2, 1);
        $message = PronosticResultMessage::create($this->pronostic, [$participation]);

        self::assertSame('Résultats du pronostic : France - Sénégal', $message->getSubject());
    }

    public function testCommonVarsContainScores(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->makeAdherent('a@b.com', 'Jean'), 2, 1);
        $message = PronosticResultMessage::create($this->pronostic, [$participation]);

        $vars = $message->getVars();
        self::assertSame('France - Sénégal', $vars['pronostic_title']);
        self::assertSame('2', $vars['result_team_1_score']);
        self::assertSame('1', $vars['result_team_2_score']);
    }

    public function testRecipientVarsForWinner(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->makeAdherent('a@b.com', 'Jean'), 2, 1);
        $message = PronosticResultMessage::create($this->pronostic, [$participation]);

        $recipientVars = $message->getRecipients()[0]->getVars();
        self::assertSame('Gagné', $recipientVars['result_status']);
        self::assertSame('1', $recipientVars['is_winner']);
        self::assertSame('2', $recipientVars['user_team_1_score']);
        self::assertSame('1', $recipientVars['user_team_2_score']);
    }

    public function testRecipientVarsForLoser(): void
    {
        $participation = new PronosticParticipation($this->pronostic, $this->makeAdherent('a@b.com', 'Jean'), 1, 0);
        $message = PronosticResultMessage::create($this->pronostic, [$participation]);

        $recipientVars = $message->getRecipients()[0]->getVars();
        self::assertSame('Perdu', $recipientVars['result_status']);
        self::assertSame('0', $recipientVars['is_winner']);
    }

    public function testThrowsWithEmptyParticipations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PronosticResultMessage::create($this->pronostic, []);
    }
}
