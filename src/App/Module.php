<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Module extends \CrazyCat\Framework\Data\Object {

    private $configRules = [
        'depends' => [ 'required' => true, 'type' => 'array' ]
    ];

    public function __construct( array $data )
    {
        parent::__construct( $this->init( $data ) );
    }

    /**
     * @param array $data
     * @return array
     */
    private function init( $data )
    {
        $data['config'] = $this->verifyConfig( $data );

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function verifyConfig( $data )
    {
        if ( !is_file( $data['dir'] . DS . 'config' . DS . 'module.php' ) ) {
            throw new \Exception( sprintf( 'Config file of module `%s` does not exist.', $data['name'] ) );
        }
        $config = require $data['dir'] . DS . 'config' . DS . 'module.php';

        if ( !is_array( $config ) ) {
            throw new \Exception( sprintf( 'Invalidated config file of module `%s`.', $data['name'] ) );
        }
        foreach ( $config as $key => $value ) {
            if ( !isset( $this->configRules[$key] ) ) {
                unset( $config[$key] );
            }
            elseif ( gettype( $value ) != $this->configRules[$key]['type'] ) {
                throw new \Exception( sprintf( 'Invalidated config file of module `%s`.', $data['name'] ) );
            }
        }
        return $config;
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
