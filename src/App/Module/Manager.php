<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Module;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Manager {

    const CACHE_NAME = 'modules';
    const CONFIG_FILE = Config::DIR . DS . 'modules.php';

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( Config $config, CacheFactory $cacheFactory, ObjectManager $objectManager )
    {
        $this->cacheFactory = $cacheFactory;
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $moduleData
     */
    private function checkDependents( $moduleData )
    {
        $enabledModules = [];
        foreach ( $moduleData as $data ) {
            if ( $data['enabled'] ) {
                $enabledModules[] = $data['name'];
            }
        }

        foreach ( $moduleData as $data ) {
            foreach ( $data['config']['depends'] as $depandModuleName ) {
                if ( !in_array( $depandModuleName, $enabledModules ) ) {
                    throw new \Exception( sprintf( 'Dependent module `%s` does not exist.', $depandModuleName ) );
                }
            }
        }
    }

    /**
     * Sort modules by dependency
     * 
     * @param array $moduleData
     */
    private function sortModules( $moduleData )
    {
        $processedModules = [];

        $sortFun = function ( $a, $b ) {
            return in_array( $a['name'], $b['config']['depends'] ) ? 1 : 0;
        };

        foreach ( $moduleData as $data ) {
            $processedModules[] = $data['name'];

            $data['config']['depends'];
        }
    }

    /**
     * @return array
     */
    private function getModulesConfig()
    {
        if ( is_file( self::CONFIG_FILE ) ) {
            $config = self::CONFIG_FILE;
        }
        if ( !isset( $config ) || !is_array( $config ) || empty( $config ) ) {
            return [];
        }
        return $config;
    }

    /**
     * @param array $config
     */
    private function updateModulesConfig( array $config )
    {
        file_put_contents( self::CONFIG_FILE, sprintf( "<?php\nreturn %s;", $this->config->toString( $config ) ) );
    }

    /**
     * @param array $moduleSource
     */
    public function init( $moduleSource )
    {
        $cache = $this->cacheFactory->create( self::CACHE_NAME );

        if ( empty( $this->modules = $cache->getData() ) ) {

            $moduleConfig = $this->getModulesConfig();

            $moduleData = [];
            foreach ( $moduleSource as $data ) {
                $module = $this->objectManager->create( Module::class, [ 'data' => $data ] );
                $module->setData( 'enabled', isset( $moduleConfig[$data['name']] ) ? $moduleConfig[$data['name']] : true  );
                $moduleData[] = $module->getData();
            }
            $this->checkDependents( $moduleData );
            $this->sortModules( $moduleData );

            /**
             * Create module config file with all modules enabled
             *     at first time running the application.
             */
            if ( empty( $moduleConfig ) ) {
                $this->updateModulesConfig( array_map( function() {
                            return true;
                        }, $moduleSource ) );
            }

            /**
             * Store in cache
             */
            $cache->setData( $moduleData )->save();
        }

        return $this->modules;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

}
