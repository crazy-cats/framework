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

    const API_ROUTE = 'rest/V1';
    const BACKEND_ROUTE_NAME = 'backend';

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

        /**
         * Check whether it routes to back-end
         */
        $pathParts = explode( '/', $this->path );
        if ( ( $pathParts[0] == $this->config->getData( Area::CODE_BACKEND )['route'] ) ) {
            $this->routeName = (!empty( $pathParts[1] ) ? $pathParts[1] : 'index' );
            if ( !( $this->moduleName = $this->getModuleNameByRoute( Area::CODE_BACKEND, $this->routeName ) ) ) {
                throw new \Exception( 'System can not find matched route.' );
            }
            $this->area->setCode( Area::CODE_BACKEND );
            $this->controllerName = !empty( $pathParts[2] ) ? $pathParts[2] : 'index';
            $this->actionName = !empty( $pathParts[3] ) ? $pathParts[3] : 'index';
        }

        /**
         * Check whether it routes to API
         */
        if ( isset( $pathParts[1] ) && ( $pathParts[0] . '/' . $pathParts[1] == self::API_ROUTE ) ) {
            if ( empty( $pathParts[2] ) ) {
                throw new \Exception( 'Undefined route.' );
            }
            $this->routeName = empty( $pathParts[2] );
            if ( !( $this->moduleName = $this->getModuleNameByRoute( Area::CODE_API, $this->routeName ) ) ) {
                throw new \Exception( 'System can not find matched route.' );
            }
            $this->area->setCode( Area::CODE_API );
            $this->controllerName = !empty( $pathParts[3] ) ? $pathParts[3] : 'index';
            $this->actionName = !empty( $pathParts[4] ) ? $pathParts[4] : 'index';
        }

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
            $this->area->setCode( Area::CODE_FRONTEND );
            $this->controllerName = !empty( $pathParts[1] ) ? $pathParts[1] : 'index';
            $this->actionName = !empty( $pathParts[2] ) ? $pathParts[2] : 'index';
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
     * @return mixed
     */
    public function getParam( $key )
    {
        return isset( $this->requestData[$key] ) ? $this->requestData[$key] : null;
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
