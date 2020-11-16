<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Symfony\Component\Translation\TranslatorInterface;

class ElectedRepresentativeTagsBuilder
{
    public const TRANSLATION_PREFIX = 'elected_representative.mailchimp_tag.';

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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

        foreach ($electedRepresentative->getUserListDefinitions() as $userListDefinition) {
            $trad = $this->translateKey($userListDefinition->getCode());
            if ($trad === $userListDefinition->getCode()) {
                $trad = sprintf('[L] %s', $userListDefinition->getLabel());
            }

            $translatedTags[] = $trad;
        }

        foreach ($electedRepresentative->getCurrentLabels() as $label) {
            $tags[] = $label->getName();
        }

        return array_unique(array_merge(
            $translatedTags,
            array_map([$this, 'translateKey'], array_unique($tags)),
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
