<?php

namespace App\Utils;

abstract class PhpConfigurator
{
    /**
     * @param int $timeLimit - default: 10 minutes
     */
    public static function disableMemoryLimit(int $timeLimit = 600): void
    {
        ini_set('memory_limit', -1);
        set_time_limit($timeLimit);
    }
}
