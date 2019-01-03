<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Request extends \CrazyCat\Framework\App\Io\AbstractRequest {

    const AJAX_PARAM = 'ajax';
    const API_ROUTE = 'rest/V1';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    protected $config;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $getData;

    /**
     * @var array
     */
    protected $postData;

    /**
     * @var array
     */
    protected $requestData;

    /**
     * @var string
     */
    protected $path;

    public function __construct( Area $area, Config $config, ModuleManager $moduleManager, EventManager $eventManager, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $areaCode
     * @param string $route
     * @return string|null
     */
    protected function getModuleNameByRoute( $areaCode, $route )
    {
        foreach ( $this->moduleManager->getEnabledModules() as $module ) {
            $moduleRoutes = $module->getData( 'config' )['routes'];
            if ( isset( $moduleRoutes[$areaCode] ) && $moduleRoutes[$areaCode] == $route ) {
                return $module->getData( 'name' );
            }
        }
        return null;
    }

    /**
     * @param array $pathParts
     * @param int $startPos
     * @return void
     */
    protected function pushPathParamsToRequest( array $pathParts, $startPos )
    {
        if ( !isset( $pathParts[$startPos] ) ) {
            return;
        }
        for ( $pos = $startPos; $pos < count( $pathParts ); $pos += 2 ) {
            $key = $pathParts[$pos];
            if ( !isset( $this->requestData[$key] ) ) {
                $this->requestData[$key] = isset( $pathParts[$pos + 1] ) ? $pathParts[$pos + 1] : null;
            }
        }
    }

    /**
     * @return void
     */
    public function process()
    {
        $server = filter_input_array( INPUT_SERVER );
        $pathRoot = dirname( $server['SCRIPT_NAME'] );
        $filePath = explode( '?', ( isset( $server['HTTP_X_REWRITE_URL'] ) ? $server['HTTP_X_REWRITE_URL'] : $server['REQUEST_URI'] ) )[0];
        $this->path = trim( ( strpos( $filePath, $server['SCRIPT_NAME'] ) !== false ) ?
                substr( $filePath, strlen( $server['SCRIPT_NAME'] ) ) :
                substr( $filePath, strlen( $pathRoot ) ), '/' );

        $getData = filter_input_array( INPUT_GET ) ?: [];
        $this->postData = filter_input_array( INPUT_POST ) ?: [];
        $this->requestData = array_merge( $getData, $this->postData );

        $pathParts = explode( '/', $this->path );

        /**
         * Check whether it routes to back-end
         */
        if ( ( $pathParts[0] == $this->config->getData( Area::CODE_BACKEND )['route'] ) ) {
            $this->area->setCode( Area::CODE_BACKEND );
            $this->routeName = (!empty( $pathParts[1] ) ? $pathParts[1] : 'system' ); // system is backend route name of core module
            if ( !( $this->moduleName = $this->getModuleNameByRoute( Area::CODE_BACKEND, $this->routeName ) ) ) {
                throw new \Exception( 'System can not find matched route.' );
            }
            $this->controllerName = !empty( $pathParts[2] ) ? $pathParts[2] : 'index';
            $this->actionName = !empty( $pathParts[3] ) ? $pathParts[3] : 'index';
            $this->pushPathParamsToRequest( $pathParts, 4 );
        }

        /**
         * Check whether it routes to API
         */
        else if ( isset( $pathParts[1] ) && ( $pathParts[0] . '/' . $pathParts[1] == self::API_ROUTE ) ) {
            $this->area->setCode( Area::CODE_API );
            if ( empty( $pathParts[2] ) || empty( $pathParts[3] ) || empty( $pathParts[4] ) ) {
                throw new \Exception( 'Route undefined.' );
            }
            $this->routeName = $pathParts[2];
            if ( !( $this->moduleName = $this->getModuleNameByRoute( Area::CODE_API, $this->routeName ) ) ) {
                throw new \Exception( 'System can not find matched route.' );
            }
            $this->controllerName = $pathParts[3];
            $this->actionName = $pathParts[4];
        }

        /**
         * A HTTP request must be one of API, backend and frontend request
         */
        else {
            $this->area->setCode( Area::CODE_FRONTEND );

            /**
             * Prepare an event for modules to add router
             */
            $this->eventManager->dispatch( 'process_http_request', [ 'request' => $this ] );

            /**
             * If it does not meet any route defined in modules, use default route rule
             */
            if ( $this->moduleName === null ) {
                $this->routeName = (!empty( $pathParts[0] ) ? $pathParts[0] : 'index' );
                if ( !( $this->moduleName = $this->getModuleNameByRoute( Area::CODE_FRONTEND, $this->routeName ) ) ) {
                    throw new \Exception( 'System can not find matched route.' );
                }
                $this->controllerName = !empty( $pathParts[1] ) ? $pathParts[1] : 'index';
                $this->actionName = !empty( $pathParts[2] ) ? $pathParts[2] : 'index';
                $this->pushPathParamsToRequest( $pathParts, 3 );
            }
        }

        return $this->getResponse();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getPost( $key = null )
    {
        return ( $key === null ) ? $this->postData :
                ( isset( $this->postData[$key] ) ? $this->postData[$key] : null );
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getParam( $key, $default = null )
    {
        return isset( $this->requestData[$key] ) ? $this->requestData[$key] : $default;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->requestData;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam( $key, $value )
    {
        $this->requestData[$key] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getHeader( $name )
    {
        if ( $this->headers === null ) {
            if ( function_exists( 'getallheaders' ) ) {
                $this->headers = getallheaders();
            }
            else {
                $this->headers = [];
                foreach ( filter_input_array( INPUT_SERVER ) as $name => $value ) {
                    if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
                        $this->headers[str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) )] = $value;
                    }
                }
            }
        }
        return isset( $this->headers[$name] ) ? $this->headers[$name] : null;
    }

    /**
     * @param string $moduleName
     * @return $this
     */
    public function setModuleName( $moduleName )
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @param string $routeName
     * @return $this
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = $routeName;
        return $this;
    }

    /**
     * @param string $controllerName
     * @return $this
     */
    public function setControllerName( $controllerName )
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @param string $actionName
     * @return $this
     */
    public function setActionName( $actionName )
    {
        $this->actionName = $actionName;
        return $this;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getFullPath( $separator = '_' )
    {
        return $this->getRouteName() .
                $separator .
                $this->getControllerName() .
                $separator .
                $this->getActionName();
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Response
     */
    public function getResponse()
    {
        if ( $this->response === null ) {
            $this->response = $this->objectManager->get( Response::class );
        }
        return $this->response;
    }

}
