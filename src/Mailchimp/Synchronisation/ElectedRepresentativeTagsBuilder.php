<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Symfony\Component\Translation\TranslatorInterface;

class ElectedRepresentativeTagsBuilder
{
    public const ADHERENT_TAG = 'adherent';
    private const TRANSLATION_PREFIX = 'elected_representative.mailchimp_tag.';

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildTags(ElectedRepresentative $electedRepresentative): array
    {
        $tags = [];

        foreach ($electedRepresentative->getCurrentMandates() as $mandate) {
            $tags[] = $mandate->getType();
        }

        foreach ($electedRepresentative->getCurrentPoliticalFunctions() as $politicalFunction) {
            $tags[] = $politicalFunction->getName();
        }

        foreach ($electedRepresentative->getUserListDefinitions() as $userListDefinition) {
            $tags[] = $userListDefinition->getCode();
        }

        foreach ($electedRepresentative->getCurrentLabels() as $label) {
            $tags[] = $label->getName();
        }

        if ($electedRepresentative->isAdherent()) {
            $tags[] = self::ADHERENT_TAG;
        }

        $translatedTags = array_map(function (string $tag) {
            return $this->translateKey($tag);
        }, array_unique($tags));

        return array_unique(array_merge(
            $translatedTags,
            $electedRepresentative->getActiveReferentTagCodes()
        ));
    }

    public function translateKey(string $key): string
    {
        $prefixedKey = self::TRANSLATION_PREFIX.$key;
        $translated = $this->translator->trans($prefixedKey);

        return $prefixedKey === $translated ? $key : $translated;
    }
}
