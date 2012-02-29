<?php

namespace Housey\Cache;

/**
 *
 * Zend Cache wrapper
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Zend implements CacheInterface
{
    /**
     * @var \Zend_Cache_Core
     */
    protected $zend;

    /**
     * @param \Memcached $memcached
     */
    public function __construct(\Zend_Cache_Core $zend)
    {
        $this->zend = $zend;
    }

    /**
     * Clean cache key
     *
     * @param string $key
     * @returns string
     */
    protected function escape($key)
    {
        return preg_replace("/[^a-zA-Z0-9_]/", "_", $key);
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
        $key = $this->escape($key);
        if (false !== $this->zend->load($key)) {
            return false;
        }
        return $this->zend->save($value, $key, array('housey'), $ttl);
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
        $key = $this->escape($key);
        return $this->zend->save($value, $key, array('housey'), $ttl);
    }

    /**
     * Get
     *
     * @param string $key
     * @return null|mixed
     */
    public function get($key)
    {
        $key = $this->escape($key);
        if (!$this->zend->test($key)) {
            return null;
        }
        return $this->zend->load($key);
    }

    /**
     * Delete
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $key = $this->escape($key);
        return $this->zend->remove($key);
    }


    /**
     * Increment
     *
     * @param string $key
     */
    public function increment($key)
    {
        $key = $this->escape($key);
        $val = $this->get($key);
        if (null === $val) {
            return false;
        }

        return $this->zend->save($val + 1, $key, array('housey'));
    }
}
