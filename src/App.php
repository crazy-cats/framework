<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

use CrazyCat\Framework\App\Component\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\Component\Manager as ComponentManager;
use CrazyCat\Framework\App\Component\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Io\Http\Response\ContentType;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class App
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Component\Manager
     */
    private $componentManager;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

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

    public function __construct()
    {
        /**
         * This is the most first initialization, to make sure all base Dependence Injection of the application
         *     can be defined correctly through configuration file `app/config/di.php`.
         */
        $this->objectManager = App\ObjectManager::getInstance();
        $diFile = DIR_APP . DS . App\Config::DIR . DS . App\ObjectManager::CONFIG_FILE;
        if (is_file($diFile)) {
            $this->objectManager->collectPreferences(require $diFile);
        }

        if (!is_file(DIR_APP . DS . App\Config::DIR . DS . App\Config::FILE)) {
            $this->objectManager->get(\CrazyCat\Framework\App\Setup\Wizard::class)->launch();
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

        $this->area = $this->objectManager->get(\CrazyCat\Framework\App\Area::class);
        $this->config = $this->objectManager->get(\CrazyCat\Framework\App\Config::class);

        /**
         * Below single instances should be created after base config is initialized
         */
        $this->componentManager = $this->objectManager->get(\CrazyCat\Framework\App\Component\Manager::class);
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
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @param string                         $areaCode
     * @throws \Exception
     */
    public function run($composerLoader, $areaCode = null)
    {
        profile_start('Run APP');

        profile_start('Initializing components');
        $components = $this->initComponents($composerLoader);
        $this->moduleManager->collectConfig();
        profile_end('Initializing components');

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
        /* @var $ioFactory \CrazyCat\Framework\App\Io\Factory */
        $ioFactory = $this->objectManager->get(\CrazyCat\Framework\App\Io\Factory::class);
        $request = $ioFactory->create($areaCode);
        $request->process();
        profile_end('Process request');

        if (($moduleName = $request->getModuleName())) {
            $this->moduleManager->getModule($moduleName)
                ->launch($this->area->getCode(), $request);
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
        [$areaCode, $themeName] = $pathArr;
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
