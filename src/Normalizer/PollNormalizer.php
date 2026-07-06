<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\VoteRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PollNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly VoteRepository $voteRepository,
        private readonly Security $security,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalized = $this->normalizer->normalize($data, $format, $context + [__CLASS__ => true]);

        $now = new \DateTimeImmutable();
        $adherent = $this->security->getUser();

        $vote = $adherent instanceof Adherent
            ? $this->voteRepository->findOneBy(['poll' => $data, 'adherent' => $adherent])
            : null;

        $normalized['has_voted'] = null !== $vote;
        $normalized['voted_choice'] = $vote?->getChoice()->getUuid()->toRfc4122();

        if ($data->canDisplayResult($now, null !== $vote)) {
            $normalized['participants'] = $this->normalizeParticipants($data);
            $normalized['result'] = $this->normalizeResult($data);
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return empty($context[__CLASS__]) && $data instanceof Poll;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Poll::class => false,
        ];
    }

    private function normalizeChoice(Choice $choice): array
    {
        return [
            'uuid' => $choice->getUuid()->toRfc4122(),
            'value' => $choice->getValue(),
        ];
    }

    private function normalizeParticipants(Poll $poll): array
    {
        return array_map(
            fn (Adherent $adherent): array => [
                'first_name' => $adherent->getFirstName(),
                'image_url' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $adherent->getImagePath()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            $this->voteRepository->findLatestVotersWithImage($poll)
        );
    }

    private function normalizeResult(Poll $poll): array
    {
        $result = $poll->getResult();

        return [
            'total' => $result['total'],
            'choices' => array_map(
                fn (array $choiceResult): array => [
                    'choice' => $this->normalizeChoice($choiceResult['choice']),
                    'count' => $choiceResult['count'],
                    'percentage' => $choiceResult['percentage'],
                ],
                $result['choices']
            ),
        ];
    }
}
