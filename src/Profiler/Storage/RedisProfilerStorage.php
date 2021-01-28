<?php

namespace App\Profiler\Storage;

use Predis\Client;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

/**
 * Copied from SncRedisBundle where it was deprecated to allow to use Redis as Profiler storage.
 *
 * @see https://github.com/snc/SncRedisBundle/issues/461
 */
class RedisProfilerStorage implements ProfilerStorageInterface
{
    private const TOKEN_PREFIX = 'sf_prof_';
    private const INDEX_NAME = 'index';

    const REDIS_SERIALIZER_NONE = 0;
    const REDIS_SERIALIZER_PHP = 1;

    /**
     * @var Client
     */
    protected $redis;

    /**
     * TTL for profiler data (in seconds).
     *
     * @var int
     */
    protected $lifetime;

    public function __construct(Client $redis, int $lifetime = 86400)
    {
        $this->redis = $redis;
        $this->lifetime = $lifetime;
    }

    public function find($ip, $url, $limit, $method, $start = null, $end = null): array
    {
        $indexName = $this->getIndexName();

        if (!$indexContent = $this->getValue($indexName)) {
            return [];
        }

        $profileList = array_reverse(explode("\n", $indexContent));
        $result = [];

        foreach ($profileList as $item) {
            if (0 === $limit) {
                break;
            }

            if ('' == $item) {
                continue;
            }

            $values = explode("\t", $item, 7);
            list($itemToken, $itemIp, $itemMethod, $itemUrl, $itemTime, $itemParent) = $values;
            $statusCode = isset($values[6]) ? $values[6] : null;

            $itemTime = (int) $itemTime;

            if ($ip && false === strpos($itemIp, $ip) || $url && false === strpos($itemUrl, $url) || $method && false === strpos($itemMethod, $method)) {
                continue;
            }

            if (!empty($start) && $itemTime < $start) {
                continue;
            }

            if (!empty($end) && $itemTime > $end) {
                continue;
            }

            $result[] = [
                'token' => $itemToken,
                'ip' => $itemIp,
                'method' => $itemMethod,
                'url' => $itemUrl,
                'time' => $itemTime,
                'parent' => $itemParent,
                'status_code' => $statusCode,
            ];
            --$limit;
        }

        return $result;
    }

    public function purge()
    {
        // delete only items from index
        $indexName = $this->getIndexName();

        $indexContent = $this->getValue($indexName);

        if (!$indexContent) {
            return false;
        }

        $profileList = explode("\n", $indexContent);

        $result = [];

        foreach ($profileList as $item) {
            if ('' == $item) {
                continue;
            }

            if (false !== $pos = strpos($item, "\t")) {
                $result[] = $this->getItemName(substr($item, 0, $pos));
            }
        }

        $result[] = $indexName;

        return $this->delete($result);
    }

    public function read($token): ?Profile
    {
        if (empty($token)) {
            return null;
        }

        $profile = $this->getValue($this->getItemName($token), self::REDIS_SERIALIZER_PHP);

        if ($profile) {
            $profile = $this->createProfileFromData($token, $profile);
        }

        return $profile;
    }

    public function write(Profile $profile): bool
    {
        $data = [
            'token' => $profile->getToken(),
            'parent' => $profile->getParentToken(),
            'children' => array_map(function ($p) {
                return $p->getToken();
            }, $profile->getChildren()),
            'data' => $profile->getCollectors(),
            'ip' => $profile->getIp(),
            'method' => $profile->getMethod(),
            'url' => $profile->getUrl(),
            'time' => $profile->getTime(),
        ];

        $profileIndexed = $this->getValue($this->getItemName($profile->getToken()));

        if ($this->setValue($this->getItemName($profile->getToken()), $data, $this->lifetime, self::REDIS_SERIALIZER_PHP)) {
            if (!$profileIndexed) {
                // Add to index
                $indexName = $this->getIndexName();

                $indexRow = implode("\t", [
                    $profile->getToken(),
                    $profile->getIp(),
                    $profile->getMethod(),
                    $profile->getUrl(),
                    $profile->getTime(),
                    $profile->getParentToken(),
                    $profile->getStatusCode(),
                ])."\n";

                return $this->appendValue($indexName, $indexRow, $this->lifetime);
            }

            return true;
        }

        return false;
    }

    protected function createProfileFromData(string $token, array $data, Profile $parent = null): Profile
    {
        $profile = new Profile($token);
        $profile->setIp($data['ip']);
        $profile->setMethod($data['method']);
        $profile->setUrl($data['url']);
        $profile->setTime($data['time']);
        $profile->setCollectors($data['data']);

        if (!$parent && $data['parent']) {
            $parent = $this->read($data['parent']);
        }

        if ($parent) {
            $profile->setParent($parent);
        }

        foreach ($data['children'] as $token) {
            if (!$token) {
                continue;
            }

            if (!$childProfileData = $this->getValue($this->getItemName($token), self::REDIS_SERIALIZER_PHP)) {
                continue;
            }

            $profile->addChild($this->createProfileFromData($token, $childProfileData, $profile));
        }

        return $profile;
    }

    protected function getItemName(string $token): string
    {
        $name = $this->prefixKey($token);

        if ($this->isItemNameValid($name)) {
            return $name;
        }

        return '';
    }

    protected function getIndexName(): string
    {
        $name = $this->prefixKey(self::INDEX_NAME);

        if ($this->isItemNameValid($name)) {
            return $name;
        }

        return '';
    }

    /**
     * Check if the item name is valid.
     *
     * @throws \RuntimeException
     */
    protected function isItemNameValid(string $name): bool
    {
        $length = \strlen($name);

        if ($length > 2147483648) {
            throw new \RuntimeException(sprintf('The Redis item key "%s" is too long (%s bytes). Allowed maximum size is 2^31 bytes.', $name, $length));
        }

        return true;
    }

    /**
     * Retrieves an item from the Redis server.
     */
    protected function getValue(string $key, int $serializer = self::REDIS_SERIALIZER_NONE)
    {
        $value = $this->redis->get($key);

        if ($value && (self::REDIS_SERIALIZER_PHP === $serializer)) {
            $value = unserialize($value);
        }

        return $value;
    }

    /**
     * Stores an item on the Redis server under the specified key.
     *
     * @return bool
     */
    protected function setValue(string $key, $value, int $expiration = 0, int $serializer = self::REDIS_SERIALIZER_NONE)
    {
        if (self::REDIS_SERIALIZER_PHP === $serializer) {
            $value = serialize($value);
        }

        return $this->redis->setex($key, $expiration, $value);
    }

    /**
     * Appends data to an existing item on the Redis server.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function appendValue(string $key, $value, int $expiration = 0)
    {
        if ($this->redis->exists($key)) {
            $this->redis->append($key, $value);

            return $this->redis->expire($key, $expiration);
        }

        return $this->redis->setex($key, $expiration, $value);
    }

    /**
     * Removes the specified keys.
     */
    protected function delete(array $keys): bool
    {
        return (bool) $this->redis->del($keys);
    }

    /**
     * Prefixes the key.
     */
    protected function prefixKey(string $key): string
    {
        return self::TOKEN_PREFIX.$key;
    }
}
