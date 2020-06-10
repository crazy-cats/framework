<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractCache extends \CrazyCat\Framework\App\Data\DataObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $isEnabled;

    public function __construct($name, $config = [])
    {
        parent::__construct([]);

        $this->name = $name;
        $this->config = $config;

        $this->init();
    }

    /**
     * @param bool|null
     * @return bool|void
     */
    public function status($enabled = null)
    {
        if ($enabled === null) {
            return $this->isEnabled;
        }
        $this->isEnabled = $enabled;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($this->isEnabled) {
            return parent::getData($key);
        }
        return null;
    }

    /**
     * @return $this
     */
    public function setData()
    {
        if ($this->isEnabled) {
            return call_user_func_array('parent::setData', func_get_args());
        }
        return $this;
    }

    /**
     * Initializing
     */
    abstract protected function init();

    /**
     * Store data into storage
     */
    abstract public function save();

    /**
     * Clear data storage
     *
     * @param bool $force
     */
    abstract public function clear($force = false);
}
