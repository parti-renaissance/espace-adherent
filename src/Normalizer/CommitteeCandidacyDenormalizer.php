<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Entity\CommitteeCandidacy;
use App\Repository\CommitteeCandidaciesGroupRepository;
use App\Repository\CommitteeMembershipRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommitteeCandidacyDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly CommitteeCandidaciesGroupRepository $candidaciesGroupRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
    ) {
    }

    public function denormalize($data, string $class, ?string $format = null, array $context = []): mixed
    {
        if (!isset($data['candidacies_group']) || !isset($data['adherent'])) {
            throw new NotNormalizableValueException('Missing "candidacies_group" or "adherent" or both keys');
        }

        if (!$list = $this->candidaciesGroupRepository->findOneByUuid($data['candidacies_group'])) {
            throw new ItemNotFoundException('Candidacies group not found');
        }

        if (!$committeeMembership = $this->committeeMembershipRepository->findMembershipFromAdherentUuidAndCommittee(Uuid::fromString($data['adherent']), $list->getCommittee())) {
            throw new ItemNotFoundException('Committee Membership not found');
        }

        $candidacy = new CommitteeCandidacy($list->getElection(), $committeeMembership->getAdherent()->getGender());
        $candidacy->setCommitteeMembership($committeeMembership);
        $candidacy->setCandidaciesGroup($list);
        $candidacy->confirm();

        return $candidacy;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            CommitteeCandidacy::class => true,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return CommitteeCandidacy::class === $type && '_api_/v3/committee_candidacies_post' === $context['operation_name'];
    }
}
