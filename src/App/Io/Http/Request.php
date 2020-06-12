<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http;

use CrazyCat\Framework\App\Area;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Request extends \CrazyCat\Framework\App\Io\AbstractRequest
{
    public const AJAX_PARAM = 'ajax';
    public const API_ROUTE = 'rest/V1';

    /**
     * @var array
     */
    protected $headers;

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

    /**
     * @var bool
     */
    private $isProcessed;

    /**
     * @param string      $areaCode
     * @param string|null $route
     * @return string|null
     */
    public function getModuleNameByRoute($areaCode, $route)
    {
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            $moduleConfig = $module->getData('config');
            if (isset($moduleConfig['routes'][$areaCode]) && $moduleConfig['routes'][$areaCode] == $route) {
                $this->routeName = $moduleConfig['routes'][$areaCode];
                return $module->getData('name');
            }
        }
        return null;
    }

    /**
     * @param array $pathParts
     * @param bool  $processParams
     * @return void
     * @throws \Exception
     */
    protected function processPath(array $pathParts, $processParams = true)
    {
        $isDefaultRoute = empty($pathParts);
        $pathParts = array_values(array_pad($pathParts, 3, 'index'));
        [$this->routeName, $this->controllerName, $this->actionName] = $pathParts;
        if ($isDefaultRoute && $this->area->getCode() == Area::CODE_BACKEND) {
            $this->routeName = 'system';
        } elseif ($this->routeName == '' && $this->area->getCode() == Area::CODE_FRONTEND) {
            $this->routeName = 'index';
        }

        $this->moduleName = $this->getModuleNameByRoute($this->area->getCode(), $this->routeName);
        if (!$this->moduleName) {
            throw new \Exception('System can not find matched route.');
        }

        if ($processParams) {
            unset($pathParts[0], $pathParts[1], $pathParts[2]);
            foreach (array_chunk($pathParts, 2) as $param) {
                if (isset($param[1])) {
                    $this->requestData[$param[0]] = $param[1];
                }
            }
        }
    }

    /**
     * @return Response
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function process()
    {
        $server = $_SERVER;
        $pathRoot = dirname($server['SCRIPT_NAME']);
        $filePath = explode('?', ($server['HTTP_X_REWRITE_URL'] ?? $server['REQUEST_URI']))[0];
        $this->path = trim(
            (strpos($filePath, $server['SCRIPT_NAME']) !== false)
                ? substr($filePath, strlen($server['SCRIPT_NAME']))
                : substr($filePath, strlen($pathRoot)),
            '/'
        );

        $getData = filter_input_array(INPUT_GET) ?: [];
        $this->postData = filter_input_array(INPUT_POST) ?: [];
        $this->requestData = array_merge($getData, $this->postData);

        /**
         * Prepare an event for modules to add router
         */
        $this->eventManager->dispatch('process_http_request_before', ['request' => $this]);
        if ($this->isProcessed) {
            $this->moduleManager->collectConfig($this->area->getCode());
            return $this->getResponse();
        }

        /**
         * Check whether it routes to API
         */
        if (strpos($this->path, self::API_ROUTE) === 0) {
            $this->area->setCode(Area::CODE_API);
            $pathParts = explode('/', $this->path);
            unset($pathParts[0], $pathParts[1]);
            if (empty($pathParts)) {
                throw new \Exception('Route undefined.');
            }
            $this->processPath($pathParts, false);
        } else {
            /**
             * Check whether it routes to back-end
             */
            $pathParts = explode('/', $this->path);
            if (isset($pathParts[0])
                && $pathParts[0] == $this->config->getValue(Area::CODE_BACKEND)['route']
            ) {
                $this->area->setCode(Area::CODE_BACKEND);
                unset($pathParts[0]);
            } else {
                /**
                 * The rest should be front-end request
                 */
                $this->area->setCode(Area::CODE_FRONTEND);
            }
            $this->processPath($pathParts);
        }

        $this->moduleManager->collectConfig($this->area->getCode());
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
    public function getPost($key = null)
    {
        return ($key === null) ? $this->postData :
            (isset($this->postData[$key]) ? $this->postData[$key] : null);
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return isset($this->requestData[$key]) ? $this->requestData[$key] : $default;
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
     * @param mixed  $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->requestData[$key] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getHeader($name)
    {
        if ($this->headers === null) {
            if (function_exists('getAllHeaders')) {
                $this->headers = array_change_key_case(getAllHeaders(), CASE_LOWER);
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $this->headers[str_replace(
                            ' ',
                            '-',
                            strtolower(str_replace('_', ' ', substr($name, 5)))
                        )] = $value;
                    }
                }
            }
        }
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * @param string $moduleName
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @param string $routeName
     * @return $this
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
        return $this;
    }

    /**
     * @param string $controllerName
     * @return $this
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @param string $actionName
     * @return $this
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return $this
     */
    public function setIsProcessed()
    {
        $this->isProcessed = true;
        return $this;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Response
     * @throws \ReflectionException
     */
    public function getResponse()
    {
        if ($this->response === null) {
            $this->response = $this->objectManager->get(Response::class);
        }
        return $this->response;
    }
}
