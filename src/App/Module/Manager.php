<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Manager {

    /**
     * @var array
     */
    private $modules = [];

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

    /**
     * @param array $modules
     */
    public function init( $modules )
    {
        foreach ( $modules as $name => $module ) {
            if ( !is_file( $module['dir'] . DS . 'config' . DS . 'module.php' ) ) {
                throw new \Exception( sprintf( 'Config file of module `%s` does not exist.', $name ) );
            }
            $config = require $module['dir'] . DS . 'config' . DS . 'module.php';
            $this->verifyConfig( $name, $config, array_keys( $modules ) );
        }
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

}
