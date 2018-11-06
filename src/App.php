<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
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
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Logger
     */
    private $logger;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

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

    public function __construct( ModuleManager $moduleManager, ComponentSetup $componentSetup, Config $config, ObjectManager $objectManager, Logger $logger )
    {
        $this->componentSetup = $componentSetup;
        $this->config = $config;
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
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

        $components = $this->componentSetup->init( $composerLoader, ROOT );
        $this->moduleManager->init( $components[ComponentSetup::TYPE_MODULE] );

        // TODO :: init modules
        // TODO :: init area
        // TODO :: translation
        // TODO :: database
    }

}
