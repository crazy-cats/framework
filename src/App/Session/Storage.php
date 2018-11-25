<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Storage {

    /**
     * @var string
     */
    private $namespace;

    public function __construct( $namespace )
    {
        $this->namespace = $namespace;

        $_SESSION[$this->namespace] = [];
    }

    /**
     * @param string $key
     */
    public function getData( $key )
    {
        return $_SESSION[$this->namespace][$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setData( $key, $value )
    {
        $_SESSION[$this->namespace][$key] = $value;
    }

    /**
     * @param string $key
     */
    public function unsetData( $key )
    {
        unset( $_SESSION[$this->namespace][$key] );
    }

    /**
     * @return void
     */
    public function clearData()
    {
        $_SESSION[$this->namespace] = [];
    }

}
