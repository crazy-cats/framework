<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Request as HttpRequest;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Url {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $httpRequest;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $currentUrl;

    public function __construct( Area $area, Config $config, HttpRequest $httpRequest )
    {
        $this->area = $area;
        $this->config = $config;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    private function getBackendUrl( $path, array $params = [] )
    {
        $tmp = trim( $path, '/' ) ?: 'index';
        $parts = explode( '/', $tmp );
        $realPath = ( ( $num = count( $parts ) ) < 3 ) ?
                ( $tmp . str_repeat( '/index', 3 - $num ) ) :
                ( $parts[0] . '/' . $parts[1] . '/' . $parts[2] );
        return $this->getBaseUrl() . $this->config->getData( 'backend' )['route'] . '/' . $realPath . ( empty( $params ) ? '' : ( '?' . http_build_query( $params ) ) );
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    private function getFrontendUrl( $path, array $params = [] )
    {
        $tmp = trim( $path, '/' ) ?: 'index';
        $parts = explode( '/', $tmp );
        $realPath = ( ( $num = count( $parts ) ) < 3 ) ?
                ( $tmp . str_repeat( '/index', 3 - $num ) ) :
                ( $parts[0] . '/' . $parts[1] . '/' . $parts[2] );
        return $this->getBaseUrl() . $realPath . ( empty( $params ) ? '' : ( '?' . http_build_query( $params ) ) );
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if ( $this->baseUrl === null ) {
            $server = filter_input_array( INPUT_SERVER );
            $scheme = ( isset( $server['HTTPS'] ) && $server['HTTPS'] != 'off' ) ? 'https' : 'http';
            $path = trim( dirname( $server['SCRIPT_NAME'] ), DS );
            $this->baseUrl = $scheme . '://' . $server['HTTP_HOST'] . '/' . ( $path ? ( trim( $path, '/' ) . '/' ) : '' );
        }
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        if ( $this->currentUrl === null ) {
            $this->currentUrl = $this->getUrl( $this->httpRequest->getRouteName() . '/' . $this->httpRequest->getControllerName() . '/' . $this->httpRequest->getActionName(), $this->httpRequest->getParams() );
        }
        return $this->currentUrl;
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    public function getUrl( $path, array $params = [] )
    {
        if ( ( $this->area->getCode() == Area::CODE_BACKEND &&
                (!isset( $params['is_frontend'] ) || $params['is_frontend'] == false ) ) ) {
            unset( $params['is_frontend'] );
            return $this->getBackendUrl( $path, $params );
        }
        else {
            unset( $params['is_frontend'] );
            return $this->getFrontendUrl( $path, $params );
        }
    }

}
