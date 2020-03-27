<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Data;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class DataObject implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string[]
     */
    protected static $underscoreCache;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }
        $result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        self::$underscoreCache[$name] = $result;
        return $result;
    }

    /**
     * @param array|null $data
     * @param array      $objectHashes
     * @return array
     */
    protected function debug($data = null, &$objectHashes = [])
    {
        if ($data === null) {
            $hash = spl_object_hash($this);
            if (isset($objectHashes[$hash])) {
                return '--- RECURSION ---';
            }
            $objectHashes[$hash] = true;
            $data = $this->getData();
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) { // numeric, string, boolean etc.
                $result[sprintf('%s (%s)', $key, gettype($value))] = $value;
            } elseif ($value instanceof Object) {
                $result[sprintf('%s (%s)', $key, get_class($value))] = $value->debug(null, $objectHashes);
            } elseif (is_array($value)) {
                $result[$key] = $this->debug($data, $objectHashes);
            }
        }
        return $result;
    }

    /**
     * @param array $a
     * @param array $b
     * @return array
     */
    protected function mergeData($a, $b)
    {
        foreach ($b as $key => $value) {
            if (!isset($a[$key]) || $a[$key] === null) {
                $a[$key] = $value;
            } elseif (is_array($value) && is_array($a[$key])) {
                $a[$key] = $this->mergeData($a[$key], $value);
            } elseif (gettype($a[$key]) == gettype($value)) {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    /**
     * @param string $method
     * @param array  $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));
                return $this->getData($key);

            case 'set':
                $key = $this->underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;
                return $this->setData($key, $value);

            case 'has':
                $key = $this->underscore(substr($method, 3));
                return isset($this->data[$key]);

            default:
                throw new \Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        return ($key !== null) ?
            (isset($this->data[(string)$key]) ? $this->data[(string)$key] : null) :
            $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data)
    {
        $this->data = $this->mergeData($this->data, $data);
        return $this;
    }

    /**
     * @param string|array $key |$data
     * @param mixed        $value
     * @return $this
     */
    public function setData()
    {
        if (func_num_args() === 1) {
            $this->data = (array)func_get_arg(0);
        } else {
            $this->data[(string)func_get_arg(0)] = func_get_arg(1);
        }
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->data = [];
        } else {
            unset($this->data[$key]);
        }
        return $this;
    }

    /**
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @param string|int $offset
     * @param mixed      $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string|int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param array $data
     * @return string
     */
    public function toString(array $data, $level = 1)
    {
        $prefix = str_repeat(' ', $level * 4);

        $arrString = [];
        foreach ($data as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'integer':
                case 'double':
                    $value = $value;
                    break;

                case 'string':
                    $value = '\'' . str_replace('\'', '\\\'', $value) . '\'';
                    break;

                case 'null':
                    $value = 'null';
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'array':
                    $value = $this->toString($value, $level + 1);
                    break;
            }
            $arrString[] = $prefix . sprintf('\'%s\' => %s', str_replace('\'', '\\\'', $key), $value);
        }

        return sprintf("[\n%s\n%s]", implode(",\n", $arrString), str_repeat(' ', ($level - 1) * 4));
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function toArray($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        }

        $array = [];
        foreach ($data as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'integer':
                case 'double':
                case 'string':
                case 'null':
                case 'boolean':
                    $array[$key] = $value;
                    break;

                case 'array':
                    $array[$key] = $this->toArray($value);
                    break;
            }
        }

        return $array;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString($this->getData());
    }

    /**
     * @return array
     */
    public function __debuginfo()
    {
        return $this->debug();
    }
}
