<?php

namespace App\Summary;

class SummaryItemDisplayOrderer
{
    /**
     * @param SummaryItemPositionableInterface[]|iterable $collection
     */
    public static function updateItem(
        iterable $collection,
        SummaryItemPositionableInterface $updatedItem,
        int $currentItemPosition,
        int $newItemPosition
    ): void {
        if (1 < \count($collection) && $currentItemPosition !== $newItemPosition) {
            foreach ($collection as $item) {
                if (!$item instanceof SummaryItemPositionableInterface) {
                    throw new \InvalidArgumentException(sprintf('Expected instance of "%s", got "%s".', SummaryItemPositionableInterface::class, \is_object($item) ? \get_class($item) : \gettype($item)));
                }

                if ($updatedItem === $item) {
                    continue;
                }

                $itemPosition = $item->getDisplayOrder();

                if ($itemPosition < $currentItemPosition) {
                    if ($itemPosition >= $newItemPosition) {
                        $item->setDisplayOrder(++$itemPosition);
                    }

                    continue;
                }

                if ($itemPosition <= $newItemPosition) {
                    $item->setDisplayOrder(--$itemPosition);
                }
            }
        }
    }

    /**
     * @param SummaryItemPositionableInterface[]|iterable $collection
     */
    public static function insertItem(iterable $collection, int $newPosition): void
    {
        foreach ($collection as $item) {
            if (!$item instanceof SummaryItemPositionableInterface) {
                throw new \InvalidArgumentException(sprintf('Expected instance of "%s", got "%s".', SummaryItemPositionableInterface::class, \is_object($item) ? \get_class($item) : \gettype($item)));
            }

            if ($newPosition <= ($order = $item->getDisplayOrder())) {
                $item->setDisplayOrder(++$order);
            }
        }
    }

    /**
     * @param SummaryItemPositionableInterface[]|iterable $collection
     */
    public static function removeItem(iterable $collection, SummaryItemPositionableInterface $removedItem): void
    {
        $position = $removedItem->getDisplayOrder();
        foreach ($collection as $item) {
            if (!$item instanceof SummaryItemPositionableInterface) {
                throw new \InvalidArgumentException(sprintf('Expected instance of "%s", got "%s".', SummaryItemPositionableInterface::class, \is_object($item) ? \get_class($item) : \gettype($item)));
            }

            if ($removedItem === $item) {
                continue;
            }

            if ($position < ($order = $item->getDisplayOrder())) {
                $item->setDisplayOrder(--$order);
            }
        }
    }
}
