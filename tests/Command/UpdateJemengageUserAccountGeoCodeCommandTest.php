<?php

namespace Tests\App\Command;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\AbstractCommandTestCase;

#[Group('command')]
class UpdateJemengageUserAccountGeoCodeCommandTest extends AbstractCommandTestCase
{
    public function testCommand(): void
    {
        $tester = new CommandTester($this->application->find('jme-adherent:update:geocode'));
        $tester->setInputs(['yes']);
        $tester->execute([]);
        $output = $tester->getDisplay();

        $this->assertStringContainsString('Are you sure to update 1 JME account(s)? (yes/no) [no]', $output);
        $this->assertStringContainsString('1 account(s) updated', $output);
    }
}
