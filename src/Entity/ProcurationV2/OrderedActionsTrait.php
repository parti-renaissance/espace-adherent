<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait OrderedActionsTrait
{
    /**
     * @var AbstractAction[]|Collection
     */
    public Collection $actions;

    /** @return AbstractSlotAction[] */
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[SerializedName('actions')]
    public function getOrderedActions(int $limit = 3): array
    {
        $actions = $this->actions->toArray();

        uasort($actions, [static::class, 'sort']);

        return \array_slice(array_values($actions), 0, $limit);
    }

    public static function sort(AbstractAction $a, AbstractAction $b): int
    {
        $aDate = $a->date->format($format = 'Y-m-d H:i:s');
        $bDate = $b->date->format($format);

        if ($aDate === $bDate) {
            return $b->getId() <=> $a->getId();
        }

        return $b->date <=> $a->date;
    }
}
