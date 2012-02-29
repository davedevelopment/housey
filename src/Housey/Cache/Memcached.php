<?php

namespace Housey\Cache;

/**
 *
 * Memcached wrapper
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Memcached implements CacheInterface
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    /**
     * @param \Memcached $memcached
     */
    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Add
     *
     * @param string $key
     * @param mixed $value
     * @param int ttl
     * @return bool
     */
    public function add($key, $value, $ttl = null)
    {
        return $this->memcached->add($key, $value, $ttl);
    }

    /**
     * Add
     *
     * @param string $key
     * @param mixed $value
     * @param int ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->memcached->set($key, $value, $ttl);
    }

    /**
     * Get
     *
     * @param string $key
     * @return null|mixed
     */
    public function get($key)
    {
        $val = $this->memcached->get($key);

        if ($val === false) {
            if ($this->memcached->getResultCode() == \Memcached::RES_NOTFOUND) {
                return null;
            }
        }

        return $val;
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }


    /**
     * Increment
     *
     * @param string $key
     */
    public function increment($key)
    {
        return $this->memcached->increment($key);
    }
}
