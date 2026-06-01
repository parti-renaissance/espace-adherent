<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Exception\InvalidAdherentTagValueException;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentTagsConditionBuilder implements SegmentConditionBuilderInterface
{
    public function __construct(private readonly TagTranslator $tagTranslator)
    {
    }

    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        foreach (array_filter([$filter->adherentTags, $filter->electTags, $filter->staticTags]) as $tag) {
            $operator = $filter->includeFilter($tag) ? 'contains' : 'notcontain';
            $tag = ltrim($tag, '!');

            if ($tag && $tagValue = $this->transformTagValue($tag)) {
                $conditions[] = [
                    'condition_type' => 'TextMerge',
                    'op' => $operator,
                    'field' => MemberRequest::MERGE_FIELD_ADHERENT_TAGS,
                    'value' => $tagValue,
                ];
            }
        }

        return $conditions;
    }

    private function transformTagValue(string $tag): ?string
    {
        $label = $this->tagTranslator->trans($tag);

        if (str_starts_with($label, 'adherent.tag.')) {
            throw new InvalidAdherentTagValueException($tag);
        }

        // A flat hierarchical root (e.g. "adherent", "elu") keeps the " - " boundary so the
        // Mailchimp "contains" match stays scoped to its children and does not substring-match
        // another family's label. Flat leaf tags (e.g. "sympathisant") match their exact label.
        if (TagEnum::isHierarchicalRoot($tag)) {
            $label .= ' - ';
        }

        return $label;
    }
}
