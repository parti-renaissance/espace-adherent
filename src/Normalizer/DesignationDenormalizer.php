<?php

namespace App\Normalizer;

use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\String\UnicodeString;

class DesignationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_DESIGNATION_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, string $class, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Designation $designation */
        $designation = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$designation->isLimited()) {
            $designation->markAsLimited();
        }

        if (!$designation->getLabel() && $designation->customTitle) {
            $designation->setLabel($designation->customTitle);
        }

        if (array_intersect(['designation_write', 'designation_write_limited'], $context['groups'] ?? [])) {
            if ($designation->isCommitteeSupervisorType()) {
                if (!$designation->getCandidacyStartDate()) {
                    $designation->setCandidacyStartDate(new \DateTime());
                }

                if ($designation->getCandidacyEndDate() !== $voteDate = $designation->getVoteStartDate()) {
                    $designation->setCandidacyEndDate($voteDate);
                }
            } elseif ($designation->isConsultationType() || $designation->isVoteType()) {
                $designation->alertTitle = $designation->getTitle();
                $designation->alertCtaLabel = $designation->alertCtaLabel ?: 'Voir';
                $designation->alertDescription = (new UnicodeString($designation->getDescription() ?? ''))->truncate(200, '…', false);
                $designation->alertBeginAt = $designation->getVoteStartDate() ? (clone $designation->getVoteStartDate())->modify('-2 days') : null;
            }

            $designation->initCreationDate();
        }

        return $designation;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, Designation::class, true)
            && \in_array($context['operation_name'] ?? null, ['_api_/designations.{_format}_post', '_api_/designations/{uuid}_put'], true);
    }
}
