<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\EventManager;

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
        'depends' => [ 'required' => true, 'type' => 'array' ],
        'events' => [ 'required' => false, 'type' => 'array' ]
    ];

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    private $eventManager;

    public function __construct( EventManager $eventManager, array $data )
    {
        $this->eventManager = $eventManager;

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
        }

        if ( !empty( $data['config']['events'] ) ) {
            $this->assignEvents( $data );
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public function getControllerActions()
    {
        
    }

    /**
     * @return string[]
     */
    public function getBlocks()
    {
        
    }

}
