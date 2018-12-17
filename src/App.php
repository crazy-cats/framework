<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\Handler\ErrorHandler;
use CrazyCat\Framework\App\Handler\ExceptionHandler;
use CrazyCat\Framework\App\Io\Factory as IoFactory;
use CrazyCat\Framework\App\Io\Http\Response\ContentType;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component as ComponentSetup;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Translator;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class App {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

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
     * @var \CrazyCat\Framework\App\Handler\ErrorHandler
     */
    private $errorHandler;

    /**
     * @var \CrazyCat\Framework\App\Io\Factory
     */
    private $ioFactory;

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
     * @var \CrazyCat\Framework\App\Translator
     */
    private $translator;

    /**
     * Get app singleton
     * @return \CrazyCat\Framework\App
     */
    static public function getInstance()
    {
        return App\ObjectManager::getInstance()->get( self::class );
    }

    public function __construct( CacheFactory $cacheFactory, ExceptionHandler $exceptionHandler, ErrorHandler $errorHandler, DbManager $dbManager, Area $area, IoFactory $ioFactory, Translator $translator, ModuleManager $moduleManager, ComponentSetup $componentSetup, Config $config, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->cacheFactory = $cacheFactory;
        $this->componentSetup = $componentSetup;
        $this->config = $config;
        $this->dbManager = $dbManager;
        $this->errorHandler = $errorHandler;
        $this->exceptionHandler = $exceptionHandler;
        $this->ioFactory = $ioFactory;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @return array
     */
    private function initComponents( $composerLoader )
    {
        set_error_handler( [ $this->errorHandler, 'process' ] );
        set_exception_handler( [ $this->exceptionHandler, 'process' ] );

        $components = $this->componentSetup->init( $composerLoader, ROOT );
        $this->moduleManager->init( $components[ComponentSetup::TYPE_MODULE] );

        return $components;
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

        $components = $this->initComponents( $composerLoader );

        /**
         * Dependency Injections
         */
        $cacheDependencyInjections = $this->cacheFactory->create( ObjectManager::CACHE_DI_NAME );
        if ( empty( $dependencyInjections = $cacheDependencyInjections->getData() ) ) {
            $dependencyInjections = $this->moduleManager->collectDependencyInjections();
            $cacheDependencyInjections->setData( $dependencyInjections )->save();
        }
        $this->objectManager->collectPreferences( $dependencyInjections );

        /**
         * Translations will be collected on the first usage of `translate` method,
         *     so no need to worry about the area code here.
         */
        $this->translator->init( $components[ComponentSetup::TYPE_LANG] );

        /**
         * The IO factory creates a suitable request object for runtime environment,
         *     area code is specified in `process` method of the object.
         */
        $this->request = $this->ioFactory->create( $areaCode );
        $this->request->process();

        if ( $this->request->getModuleName() ) {
            $this->moduleManager->getModule( $this->request->getModuleName() )
                    ->launch( $this->area->getCode(), $this->request->getControllerName(), $this->request->getActionName() );
        }
    }

    /**
     * @param string $composerLoader
     * @return mixed
     */
    public function getStatic( $composerLoader )
    {
        $this->initComponents( $composerLoader );

        $path = filter_input( INPUT_GET, 'path' );
        $pathArr = explode( '/', $path );

        /* @var $theme \CrazyCat\Framework\App\Theme */
        list( $areaCode, $themeName ) = $pathArr;
        $theme = $this->objectManager->get( ThemeManager::class )->init()
                ->getTheme( $areaCode, $themeName );

        /* @var $module \CrazyCat\Framework\App\Module */
        if ( isset( $pathArr[4] ) && ( $module = $this->objectManager->get( ModuleManager::class )
                ->getModule( $pathArr[2] . '\\' . $pathArr[3] ) ) ) {
            $staticPath = $module['config']['namespace'] . '::' . substr( $path, strlen( $areaCode ) + strlen( $themeName ) + strlen( $module['config']['namespace'] ) + 3 );
        }
        else {
            $staticPath = substr( $path, strlen( $areaCode ) + strlen( $themeName ) + 2 );
        }

        if ( ( $filePath = $theme->getStaticPath( $staticPath ) ) ) {
            header( 'Content-Type: ' . $this->objectManager->get( ContentType::class )->getByExt( pathinfo( $filePath )['extension'] ) );
            $theme->generateStaticFile( $areaCode, $themeName, $staticPath );
            readfile( $filePath );
        }
    }

}
