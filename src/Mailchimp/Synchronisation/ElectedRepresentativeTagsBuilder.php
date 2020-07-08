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
            $tags[] = $this->translateKey($mandate->getType());
        }

        foreach ($electedRepresentative->getCurrentPoliticalFunctions() as $politicalFunction) {
            $tags[] = $this->translateKey($politicalFunction->getName());
        }

        foreach ($electedRepresentative->getUserListDefinitions() as $userListDefinition) {
            $tags[] = $this->translatekey($userListDefinition->getLabel());
        }

        foreach ($electedRepresentative->getCurrentLabels() as $label) {
            $tags[] = $this->translateKey($label->getName());
        }

        if ($electedRepresentative->isAdherent()) {
            $activeTags[] = $this->translateKey(self::ADHERENT_TAG);
        }

        return array_unique($tags);
    }

    public function translateKey(string $key): string
    {
        $prefixedKey = self::TRANSLATION_PREFIX.$key;
        $translated = $this->translator->trans($prefixedKey);

        return $prefixedKey === $translated ? $key : $translated;
    }
}
