<?php

namespace Tests\AppBundle\Controller;

/**
 * @method assertArrayHasKey($key, $array, $message = '')
 */
trait ApiControllerTestTrait
{
    protected function assertEachJsonItemContainsKey($key, $json)
    {
        $data = \GuzzleHttp\json_decode($json, true);

        foreach ($data as $k => $item) {
            $this->assertArrayHasKey($key, $item, 'Item '.$k.' of JSON payload does not have '.$key.' key');
        }
    }
}
