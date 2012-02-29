<?php

namespace Housey\Cache;

/**
 * Serial cache implementation, should only really be used for testing
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Serial implements CacheInterface
{
    /**
     * @var \Serial
     */
    protected $data;

    /**
     */
    public function __construct()
    {
        $this->data = new \ArrayObject;
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
        if ($this->data->offsetExists($key)) {
            return false;
        }

        $this->data[$key] = $value;
        return true;
    }

    /**
     * set
     *
     * @param string $key
     * @param mixed $value
     * @param int ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $this->data[$key] = $value;
        return true;
    }

    /**
     * Get
     *
     * @param string $key
     * @return null|mixed
     */
    public function get($key)
    {
        if (!$this->data->offsetExists($key)) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        unset($this->data[$key]);
        return true;
    }


    /**
     * Increment
     *
     * @param string $key
     */
    public function increment($key)
    {
        if (!$this->data->offsetExists($key)) {
            return false;
        }

        $this->data[$key]++;
    }

    /**
     * Clear the cache
     *
     */
    public function clear()
    {
        $this->data = new \ArrayObject;
    }
}
