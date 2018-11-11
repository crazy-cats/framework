<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Db;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Manager {

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Db\Connection[]
     */
    private $conns = [];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( Config $config, ObjectManager $objectManager )
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    public function init()
    {
        $dbSource = $this->config->getData( 'db' );

        if ( !isset( $dbSource['default'] ) ) {
            throw new \Exception( 'Incorrect database setting.' );
        }
    }

    /**
     * @param string $name
     * @return 
     */
    public function getConnection( $name = 'default' )
    {
        if ( !isset( $this->conns[$name] ) ) {
            $dbSource = $this->config->getData( 'db' );
            if ( !isset( $dbSource[$name] ) ) {
                throw new \Exception( 'Specified database connection does not exist.' );
            }
            switch ( $dbSource[$name]['type'] ) {

                case 'mysql' :
                    $this->conns[$name] = $this->objectManager->create( MySql::class, [ 'config' => $dbSource[$name] ] );
                    break;

                default :
                    throw new \Exception( 'Incorrect database type.' );
            }
        }
        return $this->conns[$name];
    }

}
