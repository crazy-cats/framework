<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Io\Http\Response\ContentType;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component as ComponentSetup;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class App
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\Setup\Components
     */
    private $componentSetup;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Db\Manager
     */
    private $dbManager;

    /**
     * @var \CrazyCat\Framework\App\Handler\ErrorHandler
     */
    private $errorHandler;

    /**
     * @var App\Handler\ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var \CrazyCat\Framework\App\Io\Factory
     */
    private $ioFactory;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\AbstractRequest
     */
    private $request;

    /**
     * @var \CrazyCat\Framework\App\Translator
     */
    private $translator;

    /**
     * Get app singleton
     *
     * @return \CrazyCat\Framework\App
     * @throws \ReflectionException
     */
    public static function getInstance()
    {
        return App\ObjectManager::getInstance()->get(self::class);
    }

    public function __construct(
        \CrazyCat\Framework\App\Cache\Factory $cacheFactory,
        \CrazyCat\Framework\App\Handler\ExceptionHandler $exceptionHandler,
        \CrazyCat\Framework\App\Handler\ErrorHandler $errorHandler,
        \CrazyCat\Framework\App\Db\Manager $dbManager,
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Io\Factory $ioFactory,
        \CrazyCat\Framework\App\Translator $translator,
        \CrazyCat\Framework\App\Module\Manager $moduleManager,
        \CrazyCat\Framework\App\Setup\Component $componentSetup,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->cacheFactory = $cacheFactory;
        $this->componentSetup = $componentSetup;
        $this->config = $config;
        $this->dbManager = $dbManager;
        $this->errorHandler = $errorHandler;
        $this->exceptionHandler = $exceptionHandler;
        $this->ioFactory = $ioFactory;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @return array
     * @throws \Exception
     */
    private function initComponents($composerLoader)
    {
        set_error_handler([$this->errorHandler, 'process']);
        set_exception_handler([$this->exceptionHandler, 'process']);

        profile_start('Collect components');
        $components = $this->componentSetup->init($composerLoader);
        profile_end('Collect components');

        profile_start('Initializing modules');
        $this->moduleManager->init($components[ComponentSetup::TYPE_MODULE]);
        profile_end('Initializing modules');

        return $components;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @throws \Exception
     */
    public function run($composerLoader, $areaCode = null)
    {
        profile_start('Run APP');

        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set('date.timezone', 'UTC');

        profile_start('Initializing components');
        $components = $this->initComponents($composerLoader);
        profile_end('Initializing components');

        /**
         * Dependency Injections
         */
        profile_start('Initializing dependency injections');
        $cacheDependencyInjections = $this->cacheFactory->create(ObjectManager::CACHE_DI_NAME);
        if (empty($dependencyInjections = $cacheDependencyInjections->getData())) {
            $dependencyInjections = $this->moduleManager->collectDependencyInjections();
            $cacheDependencyInjections->setData($dependencyInjections)->save();
        }
        $this->objectManager->collectPreferences($dependencyInjections);
        profile_end('Initializing dependency injections');

        /**
         * Translations will be collected on the first usage of `translate` method,
         *     so no need to worry about the area code here.
         */
        profile_start('Initializing translator');
        $this->translator->init($components[ComponentSetup::TYPE_LANG]);
        profile_end('Initializing translator');

        /**
         * The IO factory creates a suitable request object for runtime environment,
         *     area code is specified in `process` method of the object.
         */
        profile_start('Process request');
        $this->request = $this->ioFactory->create($areaCode);
        $this->request->process();
        profile_end('Process request');

        if ($this->request->getModuleName()) {
            $this->moduleManager->getModule($this->request->getModuleName())
                ->launch($this->area->getCode(), $this->request->getControllerName(), $this->request->getActionName());
        }

        profile_end('Run APP');
    }

    /**
     * @param string $composerLoader
     * @return mixed
     * @throws \Exception
     */
    public function getStatic($composerLoader)
    {
        $this->initComponents($composerLoader);

        $path = filter_input(INPUT_GET, 'path');
        $pathArr = explode('/', $path);

        /* @var $theme \CrazyCat\Framework\App\Theme */
        list($areaCode, $themeName) = $pathArr;
        $theme = $this->objectManager->get(ThemeManager::class)->init()
            ->getTheme($areaCode, $themeName);

        /* @var $module \CrazyCat\Framework\App\Module */
        if (isset($pathArr[4]) && ($module = $this->objectManager->get(ModuleManager::class)
                ->getModule($pathArr[2] . '\\' . $pathArr[3]))) {
            $staticPath = $module['config']['namespace'] . '::' . substr(
                $path,
                strlen($areaCode) + strlen($themeName) + strlen($module['config']['namespace']) + 3
            );
        } else {
            $staticPath = substr($path, strlen($areaCode) + strlen($themeName) + 2);
        }

        if (($filePath = $theme->getStaticPath($staticPath))) {
            header(
                'Content-Type: ' . $this->objectManager->get(ContentType::class)->getByExt(
                    pathinfo($filePath)['extension']
                )
            );
            $theme->generateStaticFile($areaCode, $themeName, $staticPath);
            readfile($filePath);
        }
    }
}
