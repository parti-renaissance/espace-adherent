<?php

namespace Tests\App\Summary;

use App\Summary\SummaryItemDisplayOrderer;
use App\Summary\SummaryItemPositionableInterface;
use PHPUnit\Framework\TestCase;

class SummaryItemDisplayOrdererTest extends TestCase
{
    public function testUpdateItemDoesNotOrderIfThereIsOnlyOneItem()
    {
        $item = $this->createDummyItem(1);

        SummaryItemDisplayOrderer::updateItem([$item], $item, 1, 1);

        SummaryItemDisplayOrderer::updateItem([$item], $item, 1, 2);
    }

    public function testUpdateItemChangesPositions()
    {
        $item1 = $this->createDummyItem(1, true, true, 2);
        $item2 = $this->createDummyItem(2, true, true, 3);
        $item3 = $this->createDummyItem(3, false);

        SummaryItemDisplayOrderer::updateItem([$item1, $item2, $item3], $item3, 3, 1);
    }

    public function testUpdateItemChangesPositionsAndIgnoresSuperiors()
    {
        $item1 = $this->createDummyItem(1, true, true, 2);
        $item2 = $this->createDummyItem(2);
        $item3 = $this->createDummyItem(3, true, false);

        SummaryItemDisplayOrderer::updateItem([$item1, $item2, $item3], $item2, 2, 1);
    }

    public function testInsertItemChangesPositions()
    {
        $item1 = $this->createDummyItem(1, true, true, 2);
        $item2 = $this->createDummyItem(2, true, true, 3);
        $item3 = $this->createDummyItem(3, true, true, 4);

        SummaryItemDisplayOrderer::insertItem([$item1, $item2, $item3], 1);
    }

    public function testInsertItemChangesPositionsAndIgnoresInferiors()
    {
        $item1 = $this->createDummyItem(1, true);
        $item2 = $this->createDummyItem(2, true, true, 3);
        $item3 = $this->createDummyItem(3, true, true, 4);

        SummaryItemDisplayOrderer::insertItem([$item1, $item2, $item3], 2);
    }

    public function testRemoveItemChangesPositions()
    {
        $item1 = $this->createDummyItem(1, true);
        $item2 = $this->createDummyItem(2, true, true, 1);
        $item3 = $this->createDummyItem(3, true, true, 2);

        SummaryItemDisplayOrderer::removeItem([$item1, $item2, $item3], $item1);
    }

    public function testRemoveItemChangesPositionsAndIgnoresInferiors()
    {
        $item1 = $this->createDummyItem(1, true);
        $item2 = $this->createDummyItem(2, true);
        $item3 = $this->createDummyItem(3, true);

        SummaryItemDisplayOrderer::removeItem([$item1, $item2, $item3], $item3);
    }

    private function createDummyItem(
        int $position,
        bool $willBeChecked = false,
        bool $willBeOrdered = false,
        int $newPosition = null
    ): SummaryItemPositionableInterface {
        $mock = $this->createMock(SummaryItemPositionableInterface::class);

        if ($willBeChecked) {
            $mock->expects($this->once())->method('getDisplayOrder')->willReturn($position);
        } else {
            $mock->expects($this->never())->method('getDisplayOrder');
        }

        if ($willBeOrdered) {
            $mock->expects($this->once())->method('setDisplayOrder')->with($newPosition);
        } else {
            $mock->expects($this->never())->method('setDisplayOrder');
        }

        return $mock;
    }
}
