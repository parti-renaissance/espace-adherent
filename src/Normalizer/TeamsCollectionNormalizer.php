<?php

declare(strict_types=1);

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

    public function __construct(private readonly TeamRepository $repository)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $teams = iterator_to_array($object);

        $usedTeams = $this->repository->findUsedTeams(array_map(function (Team $team) {
            return $team->getId();
        }, $teams));

        array_walk($teams, function (Team $team) use ($usedTeams) {
            $team->isDeletable = !\in_array($team->getId(), $usedTeams, true);
        });

        return $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginatorInterface::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof PaginatorInterface
            && \in_array('team_list_read', $context['groups'] ?? []);
    }
}
