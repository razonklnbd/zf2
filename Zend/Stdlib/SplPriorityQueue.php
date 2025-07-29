<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

use Serializable;

/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 */
class SplPriorityQueue extends \SplPriorityQueue implements Serializable
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param  mixed $datum
     * @param  mixed $priority
     * @return void
     */
    public function insert($datum, $priority)
    {
        if (!is_array($priority)) {
            $priority = array($priority, $this->serial--);
        }
        parent::insert($datum, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }

    public function __serialize()
    {
        return $this->serializeX();
    }
    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->serializeX());
    }
    private function serializeX()
    {
        $clone = clone $this;
        $clone->setExtractFlags(self::EXTR_BOTH);
        
        $data = array();
        foreach ($clone as $item) {
            $data[] = $item;
        }
        
        return $data;
    }

    public function __unserialize(array $data)
    {
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
    /**
     * Deserialize
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        return $this->__unserialize(unserialize($data));
    }
}
