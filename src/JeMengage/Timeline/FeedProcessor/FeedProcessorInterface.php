<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;

interface FeedProcessorInterface
{
    public function process(array $item, Adherent $user): array;

    public function supports(array $item, Adherent $user): bool;
}
