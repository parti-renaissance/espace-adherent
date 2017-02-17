<?php

namespace Tests\AppBundle\ValueObject;

use AppBundle\Utils\EmojisRemover;

class EmojisRemoverTest extends \PHPUnit_Framework_TestCase
{
    public function testEmojis()
    {
        $fixtures = file_get_contents(__DIR__.'/../../Fixtures/emojis/emojis.txt');
        $this->assertEmpty(trim(EmojisRemover::remove($fixtures)));
    }
}
