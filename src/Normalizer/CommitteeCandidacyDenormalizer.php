<?php

namespace App\Normalizer;

use _PHPStan_eb00fd21c\Nette\InvalidArgumentException;
use ApiPlatform\Exception\ItemNotFoundException;
use App\Entity\CommitteeCandidacy;
use App\Repository\CommitteeCandidaciesGroupRepository;
use App\Repository\CommitteeMembershipRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CommitteeCandidacyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly CommitteeCandidaciesGroupRepository $candidaciesGroupRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository
    ) {
    }

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        if (!isset($data['candidacies_group']) || !isset($data['adherent'])) {
            throw new InvalidArgumentException('Missing "candidacies_group" or "adherent" or both keys');
        }

        if (!$list = $this->candidaciesGroupRepository->findOneByUuid($data['candidacies_group'])) {
            throw new ItemNotFoundException();
        }

        $committeeMembership = $this->committeeMembershipRepository->findMembershipfromAdherentUuidAndCommittee($data['adherent'], $list->getCommittee());

        if ($committeeMembership) {
            $candidacy = new CommitteeCandidacy($list->getElection(), $committeeMembership->getAdherent()->getGender());
            $candidacy->setCommitteeMembership($committeeMembership);
        } else {
            $candidacy = new CommitteeCandidacy($list->getElection());
        }

        $candidacy->setCandidaciesGroup($list);
        $candidacy->confirm();

        return $candidacy;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return CommitteeCandidacy::class === $type && 'api_committee_candidacies_post_collection' === $context['operation_name'];
    }
}
