<?php

namespace App\Normalizer;

use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

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

        if (\in_array('designation_write', $context['groups'] ?? [], true)) {
            if ($designation->isCommitteeSupervisorType()) {
                if (!$designation->getCandidacyStartDate()) {
                    $designation->setCandidacyStartDate(new \DateTime());
                }

                if ($designation->getCandidacyEndDate() !== $voteDate = $designation->getVoteStartDate()) {
                    $designation->setCandidacyEndDate($voteDate);
                }

                if ($designation->getVoteStartDate()) {
                    $electionCreationDate = (clone $designation->getVoteStartDate())->modify('-15 days')->setTime(0, 0, 0);

                    if ($designation->electionCreationDate !== $electionCreationDate) {
                        $designation->electionCreationDate = $electionCreationDate;
                    }
                }
            }
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
