<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use App\Repository\DonationRepository;

class AdherentStatusTagGenerator extends AbstractTagGenerator
{
    public function __construct(private readonly DonationRepository $donationRepository)
    {
    }

    public static function getDefaultPriority(): int
    {
        return 1024;
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        if ($adherent->isBesoinDEuropeUser()) {
            return [TagEnum::SYMPATHISANT_BESOIN_D_EUROPE];
        }

        if (\in_array($adherent->getSource(), [MembershipSourceEnum::AVECVOUS, MembershipSourceEnum::JEMENGAGE])) {
            return [TagEnum::SYMPATHISANT_COMPTE_AVECVOUS_JEMENGAGE];
        }

        if (!$adherent->isRenaissanceUser()) {
            return [];
        }

        $mainTag = null;

        if (\count($adherent->getConfirmedPayments())) {
            $mainTag = sprintf(TagEnum::ADHERENT_YEAR_ELU_TAG_PATTERN, date('Y'));
        }

        $countCotisationByYear = $this->donationRepository->countCotisationByYearForAdherent($adherent);

        if ($countTotalCotisation = \count($countCotisationByYear)) {
            $lastYear = key($countCotisationByYear);

            if ($lastYear == date('Y')) {
                if (1 === $countTotalCotisation) {
                    $mainTag = sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $lastYear);
                } else {
                    $mainTag = sprintf(TagEnum::ADHERENT_YEAR_RECOTISATION_TAG_PATTERN, $lastYear);
                }
            } elseif (null === $mainTag) {
                $mainTag = sprintf(TagEnum::ADHERENT_YEAR_TAG_PATTERN, $lastYear);
            }
        }

        if ($mainTag) {
            return [$mainTag];
        }

        if ($adherent->isOtherPartyMembership()) {
            return [TagEnum::SYMPATHISANT_AUTRE_PARTI];
        }

        if (!$adherent->isV2() && $adherent->getActivatedAt() && $adherent->getActivatedAt() < new \DateTime('2022-09-17')) {
            return [TagEnum::SYMPATHISANT_COMPTE_EM];
        }

        return [TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE];
    }
}
