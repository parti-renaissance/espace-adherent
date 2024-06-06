<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;

class AuthorTransformationProcessor extends AbstractFeedProcessor
{
    public function process(array $item, Adherent $user): array
    {
        if ($user->getAuthAppVersion() < 540) {
            if (\is_array($item['author'])) {
                $item['author'] = $item['author']['first_name'].' '.$item['author']['last_name'];
            }
        } elseif (!\is_array($item['author'])) {
            $item['author'] = [
                'first_name' => explode(' ', $item['author'], 2)[0],
                'last_name' => explode(' ', $item['author'], 2)[1] ?? '',
            ];
        }

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return !empty($item['author']);
    }
}
