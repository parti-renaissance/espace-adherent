<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElectedRepresentativeTagsBuilder
{
    public const TRANSLATION_PREFIX = 'elected_representative.mailchimp_tag.';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository
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

    public function buildAdherentMandateTypes(Adherent $adherent): array
    {
        $types = $this->electedRepresentativeRepository->getAdherentMandateTypes($adherent);

        $mandateTypes = [];

        if (!empty(array_intersect($types, MandateTypeEnum::NATIONAL_MANDATES))) {
            $mandateTypes[] = MandateTypeEnum::TYPE_NATIONAL;
        }

        if (!empty(array_intersect($types, MandateTypeEnum::LOCAL_MANDATES))) {
            $mandateTypes[] = MandateTypeEnum::TYPE_LOCAL;
        }

        return $mandateTypes;
    }

    public function translateKey(string $key): string
    {
        $prefixedKey = self::TRANSLATION_PREFIX.$key;
        $translated = $this->translator->trans($prefixedKey);

        return $prefixedKey === $translated ? $key : $translated;
    }
}
