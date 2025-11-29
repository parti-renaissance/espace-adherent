<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;

class ImageTransformationProcessor extends AbstractFeedProcessor
{
    public function process(array $item, Adherent $user): array
    {
        if ($user->getAuthAppVersion() < 5130000) {
            if (\is_array($item['image'])) {
                $item['image'] = $item['image']['url'] ?? null;
            }
        } elseif (!\is_array($item['image'])) {
            $item['image'] = [
                'url' => $item['image'] ?? null,
                'width' => null,
                'height' => null,
            ];
        }

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return !empty($item['image']);
    }
}
