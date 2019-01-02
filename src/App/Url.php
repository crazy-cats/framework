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

    const ID_NAME = 'id';

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

    /**
     * @param string $path
     * @return array
     */
    public function parsePath( $path )
    {
        $pathArr = array_diff( explode( '/', trim( $path, '/' ) ), [ '' ] );

        if ( isset( $pathArr[0] ) && $pathArr[0] == $this->config->getData( Area::CODE_BACKEND )['route'] ) {
            $area = Area::CODE_BACKEND;
            array_shift( $pathArr );
        }
        elseif ( isset( $pathArr[1] ) && ( $pathArr[0] . '/' . $pathArr[1] == HttpRequest::API_ROUTE ) ) {
            $area = Area::CODE_API;
            $pathArr = array_slice( $pathArr, 2 );
        }
        else {
            $area = Area::CODE_FRONTEND;
        }

        list( $route, $controller, $action ) = array_pad( $pathArr, 3, 'index' );

        $params = [];
        foreach ( array_chunk( array_slice( $pathArr, 3 ), 2 ) as $row ) {
            $params[$row[0]] = isset( $row[1] ) ? $row[1] : null;
        }

        return [
            'area' => $area,
            'route' => $route,
            'controller' => $controller,
            'action' => $action,
            'params' => $params
        ];
    }

    /**
     * @param string $url
     * @return array|false
     */
    public function parseUrl( $url )
    {
        $infoUrl = parse_url( $url );
        $infoBaseUrl = parse_url( $this->getBaseUrl() );

        if ( $infoUrl['host'] != $infoBaseUrl['host'] ) {
            return false;
        }

        $pathUrl = !empty( $infoUrl['path'] ) ? $infoUrl['path'] : '/';
        $pathBaseUrl = !empty( $infoBaseUrl['path'] ) ? $infoBaseUrl['path'] : '/';

        if ( strpos( $pathUrl, $pathBaseUrl ) !== 0 ) {
            return false;
        }

        $params = [];
        if ( isset( $infoUrl['query'] ) ) {
            parse_str( $infoUrl['query'], $params );
        }
        $info = $this->parsePath( substr( $pathUrl, strlen( $pathBaseUrl ) ) );
        $info['params'] = array_merge( $info['params'], $params );

        return $info;
    }

    /**
     * @param string $url
     * @return boolean
     */
    public function isCurrent( $url )
    {
        if ( !( $info = $this->parseUrl( $url ) ) ) {
            return false;
        }

        if ( $this->area->getCode() == $info['area'] &&
                $this->httpRequest->getRouteName() == $info['route'] &&
                $this->httpRequest->getControllerName() == $info['controller'] &&
                $this->httpRequest->getActionName() == $info['action'] ) {
            if ( ( $this->httpRequest->getParam( self::ID_NAME ) &&
                    isset( $info['params'][self::ID_NAME] ) &&
                    $this->httpRequest->getParam( self::ID_NAME ) == $info['params'][self::ID_NAME] ) ||
                    ( $this->httpRequest->getParam( self::ID_NAME ) === null &&
                    empty( $info['params'][self::ID_NAME] ) ) ) {
                return true;
            }
        }

        return false;
    }

}
