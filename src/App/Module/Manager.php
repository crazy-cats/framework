<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Manager {

    const CACHE_NAME = 'modules';

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( CacheFactory $cacheFactory, ObjectManager $objectManager )
    {
        $this->cacheFactory = $cacheFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $moduleName
     * @param array $config
     * @param array $existModules
     */
    private function verifyConfig( $moduleName, $config, $existModules )
    {
        if ( !is_array( $config ) || !isset( $config['depends'] ) || !is_array( $config['depends'] ) ) {
            throw new \Exception( sprintf( 'Invalidated config file of module `%s`.', $moduleName ) );
        }
        foreach ( $config['depends'] as $depandModuleName ) {
            if ( !in_array( $depandModuleName, $existModules ) ) {
                throw new \Exception( sprintf( 'Dependent module `%s` does not exist.', $depandModuleName ) );
            }
        }
    }

    private function initModule( $name, $module, $config )
    {
        return [
            'name' => $name,
            'depends' => $config['depends']
        ];
    }

    private function sortModules( $modules )
    {
        
    }

    /**
     * @param array $modules
     */
    public function init( $modules )
    {
        $cache = $this->cacheFactory->create( self::CACHE_NAME );

        if ( empty( $this->modules = $cache->getData() ) ) {
            $this->modules = [];
            foreach ( $modules as $name => &$module ) {
                if ( !is_file( $module['dir'] . DS . 'config' . DS . 'module.php' ) ) {
                    throw new \Exception( sprintf( 'Config file of module `%s` does not exist.', $name ) );
                }
                $config = require $module['dir'] . DS . 'config' . DS . 'module.php';
                $this->verifyConfig( $name, $config, array_keys( $modules ) );
                $this->modules[] = $this->initModule( $name, $module, $config );
            }
            $this->sortModules( $this->modules );

            /**
             * Store in cache
             */
            $cache->setData( $this->modules )->save();
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
