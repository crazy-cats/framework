<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Cookies;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Manager {

    const SESSION_NAME = 'SID';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Cookies
     */
    private $cookies;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $request;

    public function __construct( Request $request, Area $area, Cookies $cookies, Config $config, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->config = $config;
        $this->cookies = $cookies;
        $this->objectManager = $objectManager;
        $this->request = $request;
    }

    /**
     * @return string
     */
    private function generateSessionId()
    {
        $sessionId = $this->request->getParam( self::SESSION_NAME ) ?: $this->cookies->getData( self::SESSION_NAME );
        if ( $sessionId === null || strpos( $sessionId, $this->area->getHashCode() ) !== 0 ) {
            return uniqid( $this->area->getHashCode() );
        }
        return $sessionId;
    }

    /**
     * @return void
     */
    public function init()
    {
        if ( session_status() === PHP_SESSION_ACTIVE ) {
            return;
        }
        $config = $this->config->getData( Area::CODE_GLOBAL )['session'];

        switch ( $config['type'] ) {

            case SaveHandler\Files::TYPE :
                $saveHandler = SaveHandler\Files::class;
                break;

            case SaveHandler\Memcache::TYPE :
                $saveHandler = SaveHandler\Memcache::class;
                break;

            case SaveHandler\Files::TYPE :
                $saveHandler = SaveHandler\Database::class;
                break;

            case SaveHandler\Redis::TYPE :
                $saveHandler = SaveHandler\Redis::class;
                break;

            default :
                throw new \Exception( 'Invalidated session type.' );
        }

        session_set_save_handler( $this->objectManager->get( $saveHandler, [ 'config' => $config, 'areaCode' => $this->area->getCode() ] ) );
        session_set_cookie_params( $this->cookies->getDuration(), $this->cookies->getPath(), $this->cookies->getDomain() );
        session_name( self::SESSION_NAME );
        session_id( $this->generateSessionId() );
        session_start();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }

}
