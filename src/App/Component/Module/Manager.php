<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Component\Module;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Db\MySql;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    public const CACHE_NAME = 'modules';
    public const CONFIG_FILE = 'modules.php';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\Utility\ArrayTools
     */
    private $arrayTools;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Db\Manager
     */
    private $dbManager;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $diCache;

    /**
     * @var \CrazyCat\Framework\App\Component\Module[]
     */
    private $enabledModules;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $eventsCache;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    private $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Module[]
     */
    private $modules = [];

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $modulesCache;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\Cache\Manager $cacheManager,
        \CrazyCat\Framework\App\Db\Manager $dbManager,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        \CrazyCat\Framework\Utility\ArrayTools $arrayTools
    ) {
        $this->area = $area;
        $this->arrayTools = $arrayTools;
        $this->config = $config;
        $this->dbManager = $dbManager;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;

        $this->diCache = $cacheManager->create(ObjectManager::CACHE_NAME);
        $this->eventsCache = $cacheManager->create(EventManager::CACHE_NAME);
        $this->modulesCache = $cacheManager->create(self::CACHE_NAME);
    }

    /**
     * @param array $moduleData
     * @param array $modulesData
     * @param array $treeNodes
     * @return array
     * @throws \Exception
     */
    private function getAllDependentModules($moduleData, $modulesData, $treeNodes = [])
    {
        $dependentModuleNames = [];
        foreach ($moduleData['config']['depends'] as $dependentModuleName) {
            if (in_array($dependentModuleName, $treeNodes)) {
                throw new \Exception(
                    sprintf('Meet module dependency dead loop `%s` - `%s`.', $moduleData['name'], $dependentModuleName)
                );
            }
            $tmp = $treeNodes;
            $tmp[] = $dependentModuleName;
            $dependentModuleNames = array_merge(
                $tmp,
                $this->getAllDependentModules(
                    $modulesData[$dependentModuleName],
                    $modulesData,
                    $tmp
                )
            );
        }
        return array_unique($dependentModuleNames);
    }

    /**
     * @param array $modulesData
     * @throws \Exception
     */
    private function processDependency(&$modulesData)
    {
        /**
         * Check dependency of enabled modules
         */
        $moduleNames = array_keys($modulesData);
        foreach ($modulesData as $moduleData) {
            foreach ($moduleData['config']['depends'] as $dependedModuleName) {
                if ($moduleData['name'] == $dependedModuleName) {
                    throw new \Exception(sprintf('Dependent module can not set as itself.', $dependedModuleName));
                }
                if (!in_array($dependedModuleName, $moduleNames)) {
                    throw new \Exception(sprintf('Dependent module `%s` does not exist.', $dependedModuleName));
                }
            }
        }

        /**
         * Append full dependency in modules data for
         *     dead loop checking and sorting.
         */
        $tmpModulesData = $modulesData;
        foreach ($modulesData as &$moduleData) {
            $moduleData['config']['depends'] = $this->getAllDependentModules($moduleData, $tmpModulesData);
        }

        /**
         * Sort enabled modules by dependency, high level ones run at the end.
         * Affects initialization order of events, translations etc..
         */
        usort(
            $modulesData,
            function ($a, $b) {
                return in_array($a['name'], $b['config']['depends']) ? 0 : 1;
            }
        );
    }

    /**
     * @return array
     */
    private function getModulesConfig()
    {
        $file = DIR_APP . DS . Config::DIR . DS . self::CONFIG_FILE;
        if (is_file($file)) {
            $config = require $file;
        }
        if (!isset($config) || !is_array($config) || empty($config)) {
            return [];
        }
        return $config;
    }

    /**
     * @param array $config
     */
    private function updateModulesConfig(array $config)
    {
        file_put_contents(
            DIR_APP . DS . Config::DIR . DS . self::CONFIG_FILE,
            sprintf("<?php\nreturn %s;\n", $this->arrayTools->arrayToString($config))
        );
    }

    /**
     * @param array $moduleSource
     * @throws \ReflectionException
     */
    public function init($moduleSource)
    {
        if (empty($modulesData = $this->modulesCache->getData())) {
            $moduleConfig = $this->getModulesConfig();
            $modulesData = ['enabled' => [], 'disabled' => []];
            foreach ($moduleSource as $data) {
                /* @var $module \CrazyCat\Framework\App\Component\Module */
                $module = $this->objectManager->create(Module::class, ['data' => $data]);
                $namespace = $module->getData('config')['namespace'];
                if (!isset($moduleConfig[$data['name']])) {
                    $moduleConfig[$data['name']] = true;
                }
                $module->setData('enabled', (bool)$moduleConfig[$data['name']]);
                if ($moduleConfig[$data['name']]) {
                    $modulesData['enabled'][$namespace] = $module->getData();
                } else {
                    $modulesData['disabled'][$namespace] = $module->getData();
                }
                $this->modules[$namespace] = $module;
            }
            $this->processDependency($modulesData['enabled']);
            $this->modulesCache->setData($modulesData)->save();
            $this->updateModulesConfig($moduleConfig);

            /**
             * Setup modules
             */
            $conn = $this->dbManager->getConnection();
            $tblModuleSetup = $conn->getTableName('module_setup');
            $completedExecutions = $conn->fetchCol(sprintf('SELECT `class` FROM `%s`', $tblModuleSetup));
            $completedSetupClasses = [];
            $conn->beginTransaction();
            try {
                foreach ($modulesData['enabled'] as $moduleData) {
                    if (!isset($moduleData['config']['setup'])) {
                        continue;
                    }
                    foreach ($moduleData['config']['setup'] as $setupClass) {
                        $setupClass = '\\' . trim($setupClass, '\\');
                        if (in_array($setupClass, $completedExecutions)) {
                            continue;
                        }
                        $this->objectManager->get($setupClass)->execute();
                        $completedSetupClasses[] = ['class' => $setupClass];
                    }
                }
                $conn->commitTransaction();
            } catch (\Exception $e) {
                $conn->rollbackTransaction();
                throw $e;
            }
            if (!empty($completedSetupClasses)) {
                $conn->insertArray($tblModuleSetup, $completedSetupClasses);
            }
        } else {
            foreach ($modulesData as $moduleGroupData) {
                foreach ($moduleGroupData as $moduleData) {
                    $this->modules[$moduleData['config']['namespace']] = $this->objectManager->create(
                        Module::class,
                        ['data' => $moduleData]
                    );
                }
            }
        }
    }

    /**
     * @param string $areaCode
     * @throws \ReflectionException
     */
    public function collectConfig($areaCode = Area::CODE_GLOBAL)
    {
        if ($this->diCache->hasData($areaCode)) {
            $di = $this->diCache->getData($areaCode);
            $events = $this->eventsCache->getData($areaCode);
        } else {
            $di = $events = [];
            $path = Module::CONFIG_DIR . (($areaCode == Area::CODE_GLOBAL) ? '' : (DS . $areaCode));
            foreach ($this->getEnabledModules() as $module) {
                if (is_file(($file = $module->getData('dir') . DS . $path . DS . ObjectManager::CONFIG_FILE))) {
                    $di = array_merge($di, require $file);
                }
                if (is_file(($file = $module->getData('dir') . DS . $path . DS . EventManager::CONFIG_FILE))) {
                    $events = array_merge($events, require $file);
                }
            }
            $this->diCache->setData($areaCode, $di)->save();
            $this->eventsCache->setData($areaCode, $events)->save();
        }

        $this->objectManager->collectPreferences($di);
        foreach ($events as $eventName => $observers) {
            $this->eventManager->addEvent($eventName, $observers);
        }
        $this->eventManager->dispatch(
            'after_collect_module_config',
            ['area_code' => $areaCode, 'di' => $di, 'events' => $events]
        );
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Module[]
     */
    public function getAllModules()
    {
        return $this->modules;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Module[]
     */
    public function getEnabledModules()
    {
        if ($this->enabledModules === null) {
            $this->enabledModules = [];
            $modulesData = $this->modulesCache->getData();
            foreach ($modulesData['enabled'] as $moduleData) {
                $this->enabledModules[] = $this->modules[$moduleData['config']['namespace']];
            }
        }
        return $this->enabledModules;
    }

    /**
     * @param string $namespace
     * @return \CrazyCat\Framework\App\Component\Module|null
     */
    public function getModule($namespace)
    {
        return isset($this->modules[$namespace]) ? $this->modules[$namespace] : null;
    }

    /**
     * @param string      $routeName
     * @param string|null $areaCode
     * @return \CrazyCat\Framework\App\Component\Module|null
     */
    public function getModuleByRoute($routeName, $areaCode = null)
    {
        if ($areaCode === null) {
            $areaCode = $this->area->getCode();
        }
        foreach ($this->getEnabledModules() as $module) {
            if (isset($module->getData('config')['routes'][$areaCode]) &&
                $module->getData('config')['routes'][$areaCode] == $routeName) {
                return $module;
            }
        }
        return null;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function initDb()
    {
        /* @var $conn \CrazyCat\Framework\App\Db\MySql */
        $conn = $this->dbManager->getConnection();
        $conn->createTable(
            $conn->getTableName('module_setup'),
            [
                ['name' => 'class', 'type' => MySql::COL_TYPE_VARCHAR, 'length' => 256, 'null' => false]
            ],
            [
                ['columns' => ['class'], 'type' => MySql::INDEX_UNIQUE]
            ]
        );
    }
}
