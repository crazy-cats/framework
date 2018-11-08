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
     * Check dependency of enabled modules
     * 
     * @param array $modulesData
     */
    private function checkDependency( $modulesData )
    {
        $enabledModules = [];
        foreach ( $modulesData as $data ) {
            if ( $data['enabled'] ) {
                $enabledModules[] = $data['name'];
            }
        }

        foreach ( $modulesData as $data ) {
            foreach ( $data['config']['depends'] as $dependedModuleName ) {
                if ( !in_array( $dependedModuleName, $enabledModules ) ) {
                    throw new \Exception( sprintf( 'Dependent module `%s` does not exist.', $dependedModuleName ) );
                }
            }
        }
    }

    private function getAllDepentModules( $module, $modulesData )
    {
        foreach ( $module['config']['depends'] as $dependedModuleName ) {
            
        }
    }

    /**
     * Sort enabled modules by dependency
     * 
     * @param array $modulesData
     */
    private function sortModules( $modulesData )
    {
        $sortFun = function ( $a, $b ) {
            return in_array( $a['name'], $b['config']['depends'] ) ? 1 : 0;
        };

        //$this->getAllDepentModules( $module, $modulesData );
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

        if ( empty( $modulesData = $cache->getData() ) ) {

            $moduleConfig = $this->getModulesConfig();

            /**
             * Create module config file with all modules enabled
             *     at first time running the application.
             */
            if ( empty( $moduleConfig ) ) {
                $this->updateModulesConfig( array_map( function() {
                            return true;
                        }, $moduleSource ) );
            }

            $modulesData = [ 'enabled' => [], 'disabled' => [] ];
            foreach ( $moduleSource as $data ) {
                $module = $this->objectManager->create( Module::class, [ 'data' => $data ] );
                $module->setData( 'enabled', isset( $moduleConfig[$data['name']] ) ? $moduleConfig[$data['name']] : true  );
                if ( $module->getData( 'enabled' ) ) {
                    $modulesData['enabled'][$module->getData( 'name' )] = $module->getData();
                }
                else {
                    $modulesData['disabled'][$module->getData( 'name' )] = $module->getData();
                }
                $this->modules[] = $module;
            }
            $this->checkDependency( $modulesData['enabled'] );
            $this->sortModules( $modulesData['enabled'] );
            $cache->setData( $modulesData )->save();
        }
        else {
            foreach ( $modulesData as $moduleGroupData ) {
                foreach ( $moduleGroupData as $moduleData ) {
                    $this->modules[] = $this->objectManager->create( Module::class, [ 'data' => $moduleData ] );
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getAllModules()
    {
        return $this->modules;
    }

}
