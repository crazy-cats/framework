<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Context {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    protected $config;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( Area $area, Config $config, EventManager $eventManager, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return \CrazyCat\Framework\App\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \CrazyCat\Framework\App\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

}
