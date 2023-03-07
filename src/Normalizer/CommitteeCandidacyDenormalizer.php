<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommitteeCandidacyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_COMMMITTE_CANDIDACY_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var CommitteeCandidacy $candidacy */
        $candidacy = $this->denormalizer->denormalize($data, $class, $format, $context);

        /** @var Adherent $adherent */
        $adherent = $this->denormalizer->denormalize($data['adherent'], Adherent::class, $format, $context);

        /** @var CommitteeElection $election */
        $election = $candidacy->getCandidaciesGroup()?->getElection();

        if ($election && $adherent->isRenaissanceAdherent() && ($committeeMembership = $adherent->getMembershipFor($election->getCommittee()))) {
            if ($candidacy->getCommitteeMembership() !== $committeeMembership) {
                $candidacy->setCommitteeMembership($committeeMembership);
            }

            $gender = $committeeMembership->getAdherent()->getGender();
            if ($candidacy->getGender() !== $gender) {
                $candidacy->setGender($gender);
            }

            if (!$candidacy->isConfirmed()) {
                $candidacy->confirm();
            }

            $candidacy->setCommitteeElection($election);
            $candidacy->setType($election->getDesignation()->getType());
        }

        return $candidacy;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && CommitteeCandidacy::class === $type
            && \in_array($context['operation_name'] ?? null, ['api_committee_candidacies_post_collection', 'api_committee_candidacies_put_item'], true)
        ;
    }
}
