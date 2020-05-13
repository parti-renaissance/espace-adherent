<?php

namespace Tests\App\Controller;

/**
 * @method assertArrayHasKey($key, $array, $message = '')
 */
trait ApiControllerTestTrait
{
    protected function assertEachJsonItemContainsKey($key, $json, int $excluding = null)
    {
        $data = \GuzzleHttp\json_decode($json, true);

        foreach ($data as $k => $item) {
            if (isset($excluding) && $excluding === $k) {
                continue;
            }
            $this->assertArrayHasKey($key, $item, 'Item '.$k.' of JSON payload does not have '.$key.' key');
        }
    }
}
