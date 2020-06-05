<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component;

use CrazyCat\Framework\App\Io\AbstractRequest;
use CrazyCat\Framework\Utility\File;
use CrazyCat\Framework\Utility\Tools;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Module extends \CrazyCat\Framework\App\Data\DataObject
{
    const CODE_DIR = 'code';
    const CONFIG_DIR = 'config';
    const CONFIG_FILE = 'module.php';

    /**
     * @var array
     */
    private $configRules = [
        'namespace' => ['required' => true, 'type' => 'string'],
        'depends'   => ['required' => true, 'type' => 'array'],
        'setup'     => ['required' => false, 'type' => 'array'],
        'routes'    => ['required' => false, 'type' => 'array'],
        'settings'  => ['required' => false, 'type' => 'array']
    ];

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    private $eventManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        array $data
    ) {
        $this->area = $area;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;

        parent::__construct($this->init($data));
    }

    /**
     * @param array $config
     * @return bool
     */
    private function verifyConfig($config)
    {
        if (!is_array($config)) {
            return false;
        }
        foreach ($config as $key => $value) {
            if (!isset($this->configRules[$key])
                || gettype($value) != $this->configRules[$key]['type']) {
                return false;
            }
        }
        foreach ($this->configRules as $key => $rule) {
            if ($rule['required'] && !isset($config[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function collectConfig($data)
    {
        $file = $data['dir'] . DS . self::CONFIG_DIR . DS . self::CONFIG_FILE;
        if (!is_file($file)) {
            throw new \Exception(sprintf('Config file of module `%s` does not exist.', $data['name']));
        }
        $config = require $file;
        if (!$this->verifyConfig($config)) {
            throw new \Exception(sprintf('Invalidated config file of module `%s`.', $data['name']));
        }
        return $config;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function init($data)
    {
        /**
         * Consider the module data is got from cache and skip
         *     initializing actions when it is with `config`.
         */
        if (!isset($data['config'])) {
            $data['config'] = $this->collectConfig($data);
            $data['controller_actions'] = $this->intiControllerActions($data);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array [ areaCode => [ route => className ] ]
     */
    private function intiControllerActions($data)
    {
        $controllerDir = $data['dir'] . DS . self::CODE_DIR . DS . 'Controller';
        $namespace = $data['config']['namespace'];
        $routes = $data['config']['routes'];

        $actions = [];
        foreach ($this->area->getAllowedCodes() as $areaCode) {
            $actions[$areaCode] = [];
            if (!isset($routes[$areaCode])) {
                continue;
            }

            $area = ucfirst($areaCode);
            $dir = $controllerDir . DS . $area;
            if (is_dir($dir)) {
                foreach (File::getFolders($dir) as $controller) {
                    foreach (File::getFiles($dir . DS . $controller) as $action) {
                        $action = str_replace('.php', '', $action);
                        $actions[$areaCode][strtolower(
                            $routes[$areaCode] . '/' . Tools::strToSeparated($controller) . '/' . Tools::strToSeparated(
                                $action
                            )
                        )] = $namespace . '\\Controller\\' . $area . '\\' . $controller . '\\' . $action;
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * @param string|null $areaCode
     * @return array
     */
    public function getControllerActions($areaCode = null)
    {
        $controllerActions = $this->getData('controller_actions');

        return ($areaCode === null) ? $controllerActions :
            (isset($controllerActions[$areaCode]) ? $controllerActions[$areaCode] : []);
    }

    /**
     * @param string          $areaCode
     * @param AbstractRequest $request
     * @throws \ReflectionException
     */
    public function launch($areaCode, AbstractRequest $request)
    {
        $namespace = trim($this->getData('config')['namespace'], '\\');
        $area = ucfirst($areaCode);
        $controller = str_replace(' ', '', ucwords(implode(' ', explode('_', $request->getControllerName()))));
        $action = str_replace(' ', '', ucwords(implode(' ', explode('_', $request->getActionName()))));

        $this->objectManager->create(
            sprintf('%s\Controller\%s\%s\%s', $namespace, $area, $controller, $action),
            ['request' => $request]
        )->run();
    }
}
