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

    public function __construct($name, $config = [])
    {
        parent::__construct([]);

        $this->name = $name;
        $this->config = $config;

        $this->init();
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
     */
    abstract public function clear();
}
