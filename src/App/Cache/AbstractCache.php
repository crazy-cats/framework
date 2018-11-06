<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractCache extends \CrazyCat\Framework\Data\Object {

    /**
     * @var string
     */
    protected $name;

    public function __construct( $name )
    {
        $this->name = $name;

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
}
