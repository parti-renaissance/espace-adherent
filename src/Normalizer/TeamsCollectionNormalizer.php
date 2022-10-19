<?php

namespace App\Normalizer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Team\Team;
use App\Repository\Team\TeamRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TeamsCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TEAMS_COLLECTION_NORMALIZER_ALREADY_CALLED';

    private TeamRepository $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $teams = iterator_to_array($object);

        $usedTeams = $this->repository->findUsedTeams(array_map(function (Team $team) {
            return $team->getId();
        }, $teams));

        array_walk($teams, function (Team $team) use ($usedTeams) {
            $team->isDeletable = !\in_array($team->getId(), $usedTeams, true);
        });

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            !isset($context[self::ALREADY_CALLED])
            && $data instanceof PaginatorInterface
            && \in_array('team_list_read', $context['groups'] ?? [])
        ;
    }
}
