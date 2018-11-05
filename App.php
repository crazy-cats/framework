<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component as ComponentSetup;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class App {

    /**
     * @var \CrazyCat\Framework\Setup\Components
     */
    private $componentSetup;

    /**
     * @var \CrazyCat\Framework\App\Logger
     */
    private $logger;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * Get app singleton
     * @return \CrazyCat\Framework\App
     */
    static public function getInstance()
    {
        return App\ObjectManager::getInstance()->get( self::class );
    }

    public function __construct( ComponentSetup $componentSetup, ObjectManager $objectManager, Logger $logger )
    {
        $this->componentSetup = $componentSetup;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     */
    public function run( $composerLoader )
    {
        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set( 'date.timezone', 'UTC' );

        $this->componentSetup->init( $composerLoader, ROOT );

        // TODO :: init area
    }

}
