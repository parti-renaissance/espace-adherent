<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Security\Voter\PublicationVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PublicationProcessor extends AbstractFeedProcessor
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function process(array $item, Adherent $user): array
    {
        $item['editable'] = $this->authorizationChecker->isGranted(PublicationVoter::PERMISSION_ITEM, $item['access'] ?? []);

        unset($item['access']);

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::PUBLICATION;
    }
}
