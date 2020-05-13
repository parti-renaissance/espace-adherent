<?php

namespace Tests\App\MediaGenerator\Image;

use App\MediaGenerator\Command\CitizenProjectImageCommand;
use App\MediaGenerator\Image\CitizenProjectCoverGenerator;
use App\MediaGenerator\MediaContent;
use Knp\Snappy\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Templating\EngineInterface;

class CitizenProjectCoverGeneratorTest extends TestCase
{
    public function testGenerateMethodReturnMediaContentObject(): void
    {
        $generator = new CitizenProjectCoverGenerator(
            $this->createConfiguredMock(GeneratorInterface::class, ['getOutputFromHtml' => 'binary content']),
            $this->createMock(EngineInterface::class)
        );

        $mediaContent = $generator->generate($this->createMock(CitizenProjectImageCommand::class));

        $this->assertInstanceOf(MediaContent::class, $mediaContent);
        $this->assertSame('binary content', $mediaContent->getContent());
        $this->assertSame('image/png', $mediaContent->getMimeType());
        $this->assertSame(\strlen('binary content'), $mediaContent->getSize());
    }
}
