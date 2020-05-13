<?php

namespace Test\App\MediaGenerator;

use App\MediaGenerator\MediaContent;
use PHPUnit\Framework\TestCase;

class MediaContentTest extends TestCase
{
    public function testGetContentAsDataUrl(): void
    {
        $mediaContent = new MediaContent('Hello World 😀', 'text/plain', mb_strlen('Hello World 😀'));
        $this->assertSame(
            'data:text/plain;base64,'.base64_encode('Hello World 😀'),
            $mediaContent->getContentAsDataUrl()
        );
    }
}
