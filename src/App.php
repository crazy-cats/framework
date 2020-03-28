<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Component\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\Component\Manager as ComponentManager;
use CrazyCat\Framework\App\Component\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Io\Http\Response\ContentType;
use CrazyCat\Framework\App\ObjectManager;

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
     * @var \CrazyCat\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * @var \CrazyCat\Framework\Components\Setup
     */
    private $componentManager;

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
     * @var \CrazyCat\Framework\App\Component\Module\Manager
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
     * @var \CrazyCat\Framework\App\Component\Language\Translator
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
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        \CrazyCat\Framework\App\Setup\Wizard $wizard
    ) {
        $this->area = $area;
        $this->objectManager = $objectManager;

        if (!is_file(App\Config::FILE)) {
            $wizard->launch();
        }
        $this->init();
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    private function init()
    {
        $errorHandler = $this->objectManager->get(\CrazyCat\Framework\App\Handler\ErrorHandler::class);
        $exceptionHandler = $this->objectManager->get(\CrazyCat\Framework\App\Handler\ExceptionHandler::class);
        set_error_handler([$errorHandler, 'process']);
        set_exception_handler([$exceptionHandler, 'process']);

        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set('date.timezone', 'UTC');

        $this->config = $this->objectManager->get(\CrazyCat\Framework\App\Config::class);

        /**
         * Below single instances should be created after base config is initialized
         */
        $this->cacheManager = $this->objectManager->get(\CrazyCat\Framework\App\Cache\Manager::class);
        $this->componentManager = $this->objectManager->get(\CrazyCat\Framework\App\Component\Manager::class);
        $this->dbManager = $this->objectManager->get(\CrazyCat\Framework\App\Db\Manager::class);
        $this->moduleManager = $this->objectManager->get(\CrazyCat\Framework\App\Component\Module\Manager::class);
        $this->translator = $this->objectManager->get(\CrazyCat\Framework\App\Component\Language\Translator::class);
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @return array
     * @throws \Exception
     */
    private function initComponents($composerLoader)
    {
        profile_start('Collect components');
        $components = $this->componentManager->init($composerLoader);
        profile_end('Collect components');

        profile_start('Initializing modules');
        $this->moduleManager->init($components[ComponentManager::TYPE_MODULE]);
        profile_end('Initializing modules');

        return $components;
    }

    /**
     * @param string $areaCode
     * @return void
     * @throws \ReflectionException
     */
    public function initDependencyInjection($areaCode = Area::CODE_GLOBAL)
    {
        profile_start(sprintf('Initializing %s dependency injections', $areaCode));
        $cacheDependencyInjection = $this->cacheManager->create(ObjectManager::CACHE_DI_NAME);
        if ($cacheDependencyInjection->hasData($areaCode)) {
            $dependencyInjections = $cacheDependencyInjection->getData($areaCode);
        } else {
            $dependencyInjections = $this->moduleManager->collectDependencyInjections($areaCode);
            $cacheDependencyInjection->setData($areaCode, $dependencyInjections)->save();
        }
        $this->objectManager->collectPreferences($dependencyInjections);
        profile_end(sprintf('Initializing %s dependency injections', $areaCode));
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @param string                         $areaCode
     * @throws \Exception
     */
    public function run($composerLoader, $areaCode = null)
    {
        profile_start('Run APP');

        profile_start('Initializing components');
        $components = $this->initComponents($composerLoader);
        profile_end('Initializing components');

        $this->initDependencyInjection();

        /**
         * Translations will be collected on the first usage of `translate` method,
         *     so no need to worry about the area code here.
         */
        profile_start('Initializing translator');
        $this->translator->init($components[ComponentManager::TYPE_LANG]);
        profile_end('Initializing translator');

        /**
         * The IO factory creates a suitable request object for runtime environment,
         *     area code is specified in `process` method of the object.
         */
        profile_start('Process request');
        $ioFactory = $this->objectManager->get(\CrazyCat\Framework\App\Io\Factory::class);
        $this->request = $ioFactory->create($areaCode);
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

        /* @var $theme \CrazyCat\Framework\App\Component\Theme */
        list($areaCode, $themeName) = $pathArr;
        $theme = $this->objectManager->get(ThemeManager::class)->init()
            ->getTheme($areaCode, $themeName);

        /* @var $module \CrazyCat\Framework\App\Component\Module */
        if (isset($pathArr[4]) && ($module = $this->objectManager->get(ModuleManager::class)
                ->getModule($pathArr[2] . '\\' . $pathArr[3]))) {
            $staticPath = $module['config']['namespace'] . '::' .
                substr(
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

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }
}
