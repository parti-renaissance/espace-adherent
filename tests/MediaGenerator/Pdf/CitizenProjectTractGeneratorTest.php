<?php

namespace Tests\App\MediaGenerator\Pdf;

use App\MediaGenerator\Command\CitizenProjectTractCommand;
use App\MediaGenerator\MediaContent;
use App\MediaGenerator\Pdf\CitizenProjectTractGenerator;
use Knp\Snappy\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Templating\EngineInterface;

class CitizenProjectTractGeneratorTest extends TestCase
{
    public function testGenerateMethodReturnMediaContentObject(): void
    {
        $generator = new CitizenProjectTractGenerator(
            $this->createConfiguredMock(GeneratorInterface::class, ['getOutputFromHtml' => 'binary content']),
            $this->createMock(EngineInterface::class)
        );

        $mediaContent = $generator->generate($this->createMock(CitizenProjectTractCommand::class));

        $this->assertInstanceOf(MediaContent::class, $mediaContent);
        $this->assertSame('binary content', $mediaContent->getContent());
        $this->assertSame('application/pdf', $mediaContent->getMimeType());
        $this->assertSame(\strlen('binary content'), $mediaContent->getSize());
    }
}
