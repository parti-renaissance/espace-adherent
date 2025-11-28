<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Contribution\ContributionStatusEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use App\Repository\Contribution\PaymentRepository;
use App\Repository\DonationRepository;

class AdherentStatusTagGenerator extends AbstractTagGenerator
{
    public function __construct(
        private readonly DonationRepository $donationRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return 1024;
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        $currentYear = (int) date('Y');

        if ($adherent->forcedMembership) {
            return [TagEnum::getAdherentYearTag($currentYear)];
        }

        if ($adherent->isOtherPartyMembership()) {
            return [TagEnum::SYMPATHISANT_AUTRE_PARTI];
        }

        $countCotisationByYear = $this->donationRepository->countCotisationByYearForAdherent($adherent);

        if ($countTotalCotisation = \count($countCotisationByYear)) {
            $currentYear = date('Y');
            $lastYear = key($countCotisationByYear);

            if ($lastYear == $currentYear) {
                if (1 === $countTotalCotisation) {
                    return [\sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $currentYear)];
                }

                return [\sprintf(TagEnum::ADHERENT_YEAR_RECOTISATION_TAG_PATTERN, $currentYear)];
            }

            if (\count($adherent->getConfirmedPayments()) || $adherent->hasRecentContribution()) {
                return [\sprintf(TagEnum::ADHERENT_YEAR_ELU_TAG_PATTERN, $currentYear)];
            }

            $totalContributionPaymentsByYear = $this->paymentRepository->getTotalPaymentByYearForAdherent($adherent);

            if (!empty($totalContributionPaymentsByYear)) {
                if (
                    \array_key_exists($currentYear, $totalContributionPaymentsByYear)
                    && $totalContributionPaymentsByYear[$currentYear] >= 30
                    && (
                        !$adherent->findElectedRepresentativeMandates(true)
                        || $adherent->exemptFromCotisation
                        || ContributionStatusEnum::NOT_ELIGIBLE === $adherent->getContributionStatus()
                    )
                ) {
                    return [\sprintf(TagEnum::ADHERENT_YEAR_ELU_TAG_PATTERN, $currentYear)];
                }
            }

            $allYears = array_unique(array_merge(
                array_keys($countCotisationByYear),
                array_keys($totalContributionPaymentsByYear)
            ));

            unset($allYears[$currentYear]);
            rsort($allYears);

            foreach ($allYears as $year) {
                if (\array_key_exists($year, $countCotisationByYear)) {
                    return [TagEnum::getAdherentYearTag($year)];
                }

                if (
                    \array_key_exists($year, $totalContributionPaymentsByYear)
                    && $totalContributionPaymentsByYear[$year] >= 30
                    && (
                        !$adherent->findElectedRepresentativeMandates(true)
                        || $adherent->exemptFromCotisation
                        || ContributionStatusEnum::NOT_ELIGIBLE === $adherent->getContributionStatus()
                    )
                ) {
                    return [TagEnum::getAdherentYearTag($year)];
                }
            }
        }

        if (\in_array($adherent->getSource(), [MembershipSourceEnum::AVECVOUS, MembershipSourceEnum::JEMENGAGE])) {
            return [TagEnum::SYMPATHISANT_COMPTE_AVECVOUS_JEMENGAGE];
        }

        if ($adherent->isBesoinDEuropeUser()) {
            return [TagEnum::SYMPATHISANT_BESOIN_D_EUROPE];
        }

        if (MembershipSourceEnum::LEGISLATIVE === $adherent->getSource()) {
            return [TagEnum::SYMPATHISANT_ENSEMBLE2024];
        }

        if (!$adherent->isV2() && $adherent->getRegisteredAt() < new \DateTime('2022-09-17')) {
            return [TagEnum::SYMPATHISANT_COMPTE_EM];
        }

        return [TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE];
    }
}
