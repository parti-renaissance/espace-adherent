<?php

namespace Tests\AppBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class SqliteWebTestCase extends WebTestCase
{
    protected $environment = 'test_sqlite';

    /**
     * @return string Date in the format "Jeudi 14 juin 2018, 9h00"
     */
    protected function formatEventDate(\DateTime $date): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'EEEE d LLLL Y, H');

        return ucfirst(strtolower($formatter->format($date).'h00'));
    }
}
