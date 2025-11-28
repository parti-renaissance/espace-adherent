<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use Cake\Chronos\Chronos;

class ChronosContext implements Context
{
    /**
     * @Given I freeze the clock to :dateTime
     */
    public function freezeClock(string $dateTime): void
    {
        Chronos::setTestNow($dateTime);
    }

    /**
     * @AfterScenario
     */
    public function defreezeClock(): void
    {
        Chronos::setTestNow();
    }
}
