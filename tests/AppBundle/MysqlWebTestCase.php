<?php

namespace Tests\AppBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class MysqlWebTestCase extends WebTestCase
{
    protected $environment = 'test_mysql';
}
