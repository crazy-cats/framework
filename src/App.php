<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\Io\Factory as IoFactory;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component as ComponentSetup;
use CrazyCat\Framework\App\Translation;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class App {

    const DIR = __DIR__;

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\Setup\Components
     */
    private $componentSetup;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Db\Manager
     */
    private $dbManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Factory
     */
    private $ioFactory;

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
     * @var \CrazyCat\Framework\App\Io\AbstractRequest
     */
    private $request;

    /**
     * @var \CrazyCat\Framework\App\Translation
     */
    private $translation;

    /**
     * Get app singleton
     * @return \CrazyCat\Framework\App
     */
    static public function getInstance()
    {
        return App\ObjectManager::getInstance()->get( self::class );
    }

    public function __construct( DbManager $dbManager, Area $area, IoFactory $ioFactory, Translation $translation, ModuleManager $moduleManager, ComponentSetup $componentSetup, Config $config, ObjectManager $objectManager, Logger $logger )
    {
        $this->area = $area;
        $this->componentSetup = $componentSetup;
        $this->config = $config;
        $this->dbManager = $dbManager;
        $this->ioFactory = $ioFactory;
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->translation = $translation;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return \CrazyCat\Framework\App\Translation
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     */
    public function run( $composerLoader, $areaCode = null )
    {
        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set( 'date.timezone', 'UTC' );

        $components = $this->componentSetup->init( $composerLoader, ROOT );
        $this->moduleManager->init( $components[ComponentSetup::TYPE_MODULE] );
        $this->translation->init();
        $this->request = $this->ioFactory->create( $areaCode );
        $this->request->process();

        if ( $this->request->getModuleName() ) {
            $this->moduleManager->getModule( $this->request->getModuleName() )
                    ->launch( $this->area->getCode(), $this->request->getControllerName(), $this->request->getActionName() );
        }

        // TODO :: database
    }

}
