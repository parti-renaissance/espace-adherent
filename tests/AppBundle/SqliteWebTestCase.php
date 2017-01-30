<?php

namespace Tests\AppBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class SqliteWebTestCase extends WebTestCase
{
    protected $environment = 'test_sqlite';
}
