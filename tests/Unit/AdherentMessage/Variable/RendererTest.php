<?php

declare(strict_types=1);

namespace Tests\App\Unit\AdherentMessage\Variable;

use App\AdherentMessage\Variable\ContextBuilder;
use App\AdherentMessage\Variable\Parser;
use App\AdherentMessage\Variable\Renderer;
use App\Entity\Adherent;
use App\ValueObject\Genders;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ServiceProviderInterface;

class RendererTest extends TestCase
{
    public function testRenderPlainResolvesDictionaryCodesToConnectedUserValues(): void
    {
        $result = $this->renderer()->renderPlain(
            '{{Chère/Cher Prénom}}, n° {{Numéro militant}}',
            $this->adherent()
        );

        self::assertSame('Chère Jeanne, n° ABC-234', $result);
    }

    public function testRenderPlainLeavesStringWithoutVariablesUnchanged(): void
    {
        $result = $this->renderer()->renderPlain('Sujet sans variable', $this->adherent());

        self::assertSame('Sujet sans variable', $result);
    }

    public function testRenderPlainLeavesUnknownPlaceholdersIntact(): void
    {
        $result = $this->renderer()->renderPlain('Bonjour {{Inconnu}} {{Prénom}}', $this->adherent());

        self::assertSame('Bonjour {{Inconnu}} Jeanne', $result);
    }

    private function renderer(): Renderer
    {
        // renderPlain does not use the transport-format registry, so a bare stub is enough.
        return new Renderer(new Parser(), new ContextBuilder(), $this->createStub(ServiceProviderInterface::class));
    }

    private function adherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('jeanne@test.fr'),
            'ABC-234',
            'jeanne@test.fr',
            null,
            Genders::FEMALE,
            'Jeanne',
            'Dupont',
        );
    }
}
