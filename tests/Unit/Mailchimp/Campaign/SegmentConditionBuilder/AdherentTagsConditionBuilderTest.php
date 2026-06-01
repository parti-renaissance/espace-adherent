<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Adherent\Tag\TagTranslator;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentTagsConditionBuilder;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdherentTagsConditionBuilderTest extends TestCase
{
    private TagTranslator&MockObject $tagTranslator;
    private AdherentTagsConditionBuilder $builder;

    protected function setUp(): void
    {
        $this->tagTranslator = $this->createMock(TagTranslator::class);
        $this->builder = new AdherentTagsConditionBuilder($this->tagTranslator);
    }

    public function testFlatSympathisantTagProducesConditionWithoutSeparator(): void
    {
        $this->tagTranslator
            ->expects(self::once())
            ->method('trans')
            ->with('sympathisant')
            ->willReturn('Membre')
        ;

        $filter = new AdherentMessageFilter();
        $filter->adherentTags = 'sympathisant';

        self::assertSame([
            [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => 'Membre',
            ],
        ], $this->builder->buildFromFilter($filter));
    }

    public function testFlatParentTagKeepsSeparatorBoundary(): void
    {
        // A flat *parent* tag (e.g. "adherent") must keep the " - " boundary so the Mailchimp
        // "contains" match stays scoped to its children and does not substring-match another
        // family's label (e.g. "Élu à jour" inside an adherent tag).
        $this->tagTranslator
            ->expects(self::once())
            ->method('trans')
            ->with('adherent')
            ->willReturn('Adhérent')
        ;

        $filter = new AdherentMessageFilter();
        $filter->adherentTags = 'adherent';

        self::assertSame([
            [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => 'Adhérent - ',
            ],
        ], $this->builder->buildFromFilter($filter));
    }

    public function testHierarchicalTagKeepsTranslatedValueUnchanged(): void
    {
        $this->tagTranslator
            ->expects(self::once())
            ->method('trans')
            ->with('adherent:a_jour_2024')
            ->willReturn('Adhérent - À jour 2024')
        ;

        $filter = new AdherentMessageFilter();
        $filter->adherentTags = 'adherent:a_jour_2024';

        self::assertSame([
            [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => 'Adhérent - À jour 2024',
            ],
        ], $this->builder->buildFromFilter($filter));
    }

    public function testExcludedFlatTagUsesNotContainOperator(): void
    {
        $this->tagTranslator
            ->expects(self::once())
            ->method('trans')
            ->with('sympathisant')
            ->willReturn('Membre')
        ;

        $filter = new AdherentMessageFilter();
        $filter->adherentTags = '!sympathisant';

        self::assertSame([
            [
                'condition_type' => 'TextMerge',
                'op' => 'notcontain',
                'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                'value' => 'Membre',
            ],
        ], $this->builder->buildFromFilter($filter));
    }
}
