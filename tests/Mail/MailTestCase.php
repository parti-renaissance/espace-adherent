<?php

namespace Tests\AppBundle\Mail;

use EnMarche\MailerBundle\Test\MailTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Tests\AppBundle\TestHelperTrait;

abstract class MailTestCase extends TestCase
{
    use MailTestCaseTrait;
    use TestHelperTrait;
}
