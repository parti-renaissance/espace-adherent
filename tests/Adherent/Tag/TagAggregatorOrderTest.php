<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag;

use App\Adherent\Tag\TagAggregator;
use App\Adherent\Tag\TagGenerator\AdherentElectStatusTagGenerator;
use App\Adherent\Tag\TagGenerator\AdherentStatusTagGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Guards the generator ordering invariant: the status generator must run before the elect generator,
 * because the elect generator reads the tags produced by the status generator from `previousTags`.
 * If the ordering ever flips, this test fails loudly instead of letting the elect generator read a
 * stale/empty previousTags set.
 */
class TagAggregatorOrderTest extends KernelTestCase
{
    public function testStatusGeneratorRunsBeforeElectGenerator(): void
    {
        self::bootKernel();

        $aggregator = self::getContainer()->get(TagAggregator::class);

        $generators = iterator_to_array(new \ReflectionProperty($aggregator, 'generators')->getValue($aggregator));
        $classes = array_map(static fn (object $generator): string => $generator::class, array_values($generators));

        $statusIndex = array_search(AdherentStatusTagGenerator::class, $classes, true);
        $electIndex = array_search(AdherentElectStatusTagGenerator::class, $classes, true);

        self::assertNotFalse($statusIndex, 'the status generator must be registered');
        self::assertNotFalse($electIndex, 'the elect generator must be registered');
        self::assertLessThan($electIndex, $statusIndex, 'the status generator must run before the elect generator (which reads previousTags)');
    }
}
