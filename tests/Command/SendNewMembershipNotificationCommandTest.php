<?php

namespace Tests\App\Command;

use App\Mailer\Message\Renaissance\RenaissanceNewAdherentsNotificationMessage;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class SendNewMembershipNotificationCommandTest extends AbstractCommandTestCase
{
    public function testCommandSuccess(): void
    {
        $this->runCommand('app:membership:send-notification');

        $this->assertCountMails(1, RenaissanceNewAdherentsNotificationMessage::class);
    }
}
