<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\Utility\File;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Module extends \CrazyCat\Framework\Data\Object {

    const FILE_CONFIG = 'config' . DS . 'module.php';

    /**
     * @var array
     */
    private $configRules = [
        'namespace' => [ 'required' => true, 'type' => 'string' ],
        'version' => [ 'required' => true, 'type' => 'string' ],
        'depends' => [ 'required' => true, 'type' => 'array' ],
        'events' => [ 'required' => false, 'type' => 'array' ],
        'routes' => [ 'required' => false, 'type' => 'array' ]
    ];

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    private $eventManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( Area $area, ObjectManager $objectManager, EventManager $eventManager, array $data )
    {
        $this->area = $area;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;

        parent::__construct( $this->init( $data ) );
    }

    /**
     * @param array $data
     * @return array
     */
    private function verifyConfig( $data )
    {
        if ( !is_file( $data['dir'] . DS . self::FILE_CONFIG ) ) {
            throw new \Exception( sprintf( 'Config file of module `%s` does not exist.', $data['name'] ) );
        }
        $config = require $data['dir'] . DS . self::FILE_CONFIG;

        if ( !is_array( $config ) ) {
            throw new \Exception( sprintf( 'Invalidated config file of module `%s`.', $data['name'] ) );
        }
        foreach ( $config as $key => $value ) {
            if ( !isset( $this->configRules[$key] ) ) {
                unset( $config[$key] );
            }
            elseif ( gettype( $value ) != $this->configRules[$key]['type'] ) {
                throw new \Exception( sprintf( 'Invalidated setting `%s` of module `%s`.', $key, $data['name'] ) );
            }
        }
        foreach ( $this->configRules as $key => $rule ) {
            if ( $rule['required'] && !isset( $config[$key] ) ) {
                throw new \Exception( sprintf( 'Setting `%s` of module `%s` is required.', $key, $data['name'] ) );
            }
        }
        return $config;
    }

    /**
     * @param array $events
     */
    private function assignEvents( array $events )
    {
        foreach ( $events as $eventName => $observer ) {
            $this->eventManager->addEvent( $eventName, $observer );
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function init( $data )
    {
        /**
         * Consider the module data is got from cache and skip
         *     initializing actions when it is with `config`.
         */
        if ( !isset( $data['config'] ) ) {
            $data['config'] = $this->verifyConfig( $data );
            $data['controller_actions'] = $this->intiControllerActions( $data );
        }

        if ( !empty( $data['config']['events'] ) ) {
            $this->assignEvents( $data );
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array [ areaCode => [ route => className ] ]
     */
    private function intiControllerActions( $data )
    {
        $controllerDir = $data['dir'] . DS . 'code' . DS . 'Controller';
        $namespace = $data['config']['namespace'];
        $routes = $data['config']['routes'];

        $actions = [];
        foreach ( $this->area->getAllowedCodes() as $areaCode ) {
            $actions[$areaCode] = [];
            if ( !isset( $routes[$areaCode] ) ) {
                continue;
            }

            $area = ucfirst( $areaCode );
            $dir = $controllerDir . DS . $area;
            if ( is_dir( $dir ) ) {
                foreach ( File::getFolders( $dir ) as $controller ) {
                    foreach ( File::getFiles( $dir . DS . $controller ) as $action ) {
                        $action = str_replace( '.php', '', $action );
                        $actions[$areaCode][strtolower( $routes[$areaCode] . '/' . $controller . '/' . $action )] = $namespace . '\\Controller\\' . $area . '\\' . $controller . '\\' . $action;
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * @return void
     */
    public function upgrade( &$moduleConfig )
    {
        if ( (!isset( $moduleConfig['version'] ) ||
                version_compare( $moduleConfig['version'], $this->data['config']['version'] ) < 0 ) &&
                class_exists( ( $setupClass = $this->data['config']['namespace'] . '\Setup\Upgrade' ) ) ) {
            $this->objectManager->get( $setupClass )->execute();
        }
        $moduleConfig['version'] = $this->data['config']['version'];
    }

    /**
     * @param string|null $areaCode
     * @return array
     */
    public function getControllerActions( $areaCode = null )
    {
        $controllerActions = $this->getData( 'controller_actions' );

        return ( $areaCode === null ) ? $controllerActions :
                ( isset( $controllerActions[$areaCode] ) ? $controllerActions[$areaCode] : [] );
    }

    /**
     * @return string[]
     */
    public function getBlocks()
    {
        return [];
    }

    /**
     * @param string $areaCode
     * @param string $controllerName
     * @param string $actionName
     */
    public function launch( $areaCode, $controllerName, $actionName )
    {
        $namespace = trim( $this->getData( 'config' )['namespace'], '\\' );
        $area = ucfirst( $areaCode );
        $controller = str_replace( ' ', '', ucwords( implode( ' ', explode( '_', $controllerName ) ) ) );
        $action = ucfirst( $actionName );

        $this->objectManager->create( sprintf( '%s\Controller\%s\%s\%s', $namespace, $area, $controller, $action ) )->execute();
    }

}
