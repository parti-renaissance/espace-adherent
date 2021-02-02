<?php

namespace Tests\App\Controller;

/**
 * @method assertArrayHasKey($key, $array, $message = '')
 */
trait ApiControllerTestTrait
{
    protected function getAccessToken(
        string $clientUuid,
        string $clientSecret,
        string $grantType,
        string $scope,
        string $username = null,
        string $userPassword = null
    ): ?string {
        $params = [
            'client_id' => $clientUuid,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
            'scope' => $scope,
        ];

        if (!empty($username)) {
            $params['username'] = $username;
            $params['password'] = $userPassword;
        }

        $this->client->request('POST', '/oauth/v2/token', $params);

        return json_decode($this->client->getResponse()->getContent(), true)['access_token'] ?? null;
    }

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
