<?php

namespace Housey\Cache;

/**
 * CacheInterface
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
interface CacheInterface
{
    /**
     * Should behave like the memcached add method, in that false is returned if
     * the object already exists
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function add($key, $value, $ttl = null);

    public function set($key, $value, $ttl = null);

    /**
     * !Must return null if not found
     *
     * @param string $key
     * @return null|mixed
     */
    public function get($key);

    public function delete($key);

    public function increment($key);
}



