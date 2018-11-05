<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Module {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $dir;

    public function __construct( $name, $dir )
    {
        $this->dir = $dir;
        $this->name = $name;
    }

}
