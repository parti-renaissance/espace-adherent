<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TagTranslatorTest extends KernelTestCase
{
    /**
     * The flat lifecycle tags (contact / user) carry no ":" so they bypass the static-label path and
     * resolve through the `adherent.tag.<tag>` fallback key — exactly like the sympathisant tag used here
     * as a control. Guards that the labels surfaced in Mailchimp sync and the API normalizer are correct.
     */
    #[DataProvider('provideFlatTagLabels')]
    public function testFlatTagTranslatesToHumanLabel(string $tag, string $expected): void
    {
        self::bootKernel();

        $translator = self::getContainer()->get(TagTranslator::class);

        self::assertSame($expected, $translator->trans($tag, false));
    }

    public static function provideFlatTagLabels(): iterable
    {
        yield 'sympathisant (control)' => [TagEnum::SYMPATHISANT, 'Sympathisant'];
        yield 'contact' => [TagEnum::CONTACT, 'Contact'];
        yield 'user' => [TagEnum::USER, 'Utilisateur simple'];
    }
}
