<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElectedRepresentativeTagsBuilder
{
    public const TRANSLATION_PREFIX = 'elected_representative.mailchimp_tag.';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildTags(ElectedRepresentative $electedRepresentative): array
    {
        $tags = [];
        $translatedTags = [];

        foreach ($electedRepresentative->getCurrentMandates() as $mandate) {
            $tags[] = $mandate->getType();
        }

        foreach ($electedRepresentative->getCurrentPoliticalFunctions() as $politicalFunction) {
            $tags[] = $politicalFunction->getName();
        }

        foreach ($electedRepresentative->getCurrentLabels() as $label) {
            $tags[] = $label->getName();
        }

        return array_unique(array_merge(
            $translatedTags,
            array_map([$this, 'translateKey'], array_unique($tags)),
        ));
    }

    public function translateKey(string $key): string
    {
        $prefixedKey = self::TRANSLATION_PREFIX.$key;
        $translated = $this->translator->trans($prefixedKey);

        return $prefixedKey === $translated ? $key : $translated;
    }
}
