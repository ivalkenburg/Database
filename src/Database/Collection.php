<?php

namespace IgorV\Database;

class Collection implements \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate
{
    /**
     * Collection of items.
     *
     * @var array
     */
    protected $collection;

    /**
     * @param array $collection
     */
    public function __construct($collection = [])
    {
        $this->collection = $collection;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->collection[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Return collection as JSON.
     *
     * @return string
     */
    public function asJson()
    {
        return json_encode($this->asArray());
    }

    /**
     * Return collection as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return array_map(function ($row) {
            return (array) $row;
        }, $this->collection);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * Pluck single column from all items as a new collection.
     *
     * @param $column
     *
     * @return static
     */
    public function pluck($column)
    {
        return new static(array_column($this->collection, $column));
    }

    /**
     * Filter through collection and return a new collection.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function filter(callable $callback)
    {
        return new static(array_filter($this->collection, $callback));
    }
}
