<?php

namespace IgorV\Database;

class ResultSet implements \JsonSerializable, \ArrayAccess {

    /**
     * Return result set as JSON.
     *
     * @return string
     */
    public function asJson()
    {
        return json_encode($this->asArray());
    }

    /**
     * Return result set as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return (array) $this;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}
