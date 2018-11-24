<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

use CrazyCat\Framework\App\Config;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Manager {

    const DIR = DIR_VAR . DS . 'session';
    const SESSION_NAME = 'SID';

    /**
     * Session types
     */
    const TYPE_DATABASE = 'database';
    const TYPE_FILES = 'files';
    const TYPE_MEMCACHE = 'memcache';
    const TYPE_REDIS = 'redis';

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    public function __construct( Config $config )
    {
        $this->config = $config;
    }

    public function init()
    {
        if ( session_status() === PHP_SESSION_ACTIVE ) {
            return;
        }

        session_name( self::SESSION_NAME );

        switch ( $this->config->getData( 'session' )['type'] ) {

            case self::TYPE_DATABASE :
                $this->initDatabase();
                break;

            case self::TYPE_FILES :
                $this->initFiles();
                break;

            case self::TYPE_MEMCACHE :
                $this->initMemcache();
                break;

            case self::TYPE_REDIS :
                $this->initRedis();
                break;
        }

        session_start();
    }

    /**
     * @return void
     */
    private function initDatabase()
    {
        
    }

    /**
     * @return void
     */
    private function initFiles()
    {
        if ( !is_dir( self::DIR ) ) {
            mkdir( self::DIR, 0755, true );
        }
        session_module_name( self::TYPE_FILES );
        session_save_path( self::DIR );
    }

    /**
     * @return void
     */
    private function initMemcache()
    {
        
    }

    /**
     * @return void
     */
    private function initRedis()
    {
        
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

}
