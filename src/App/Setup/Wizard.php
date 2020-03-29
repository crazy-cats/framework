<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Component\Manager as ComponentManager;
use CrazyCat\Framework\App\Config as AppConfig;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Wizard
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->objectManager = $objectManager;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function setupFromCli()
    {
        $consoleInput = $this->objectManager->create(ArgvInput::class);
        $consoleOutput = $this->objectManager->create(ConsoleOutput::class);

        /* @var $consoleApplication \Symfony\Component\Console\Application */
        $consoleApplication = $this->objectManager->create(ConsoleApplication::class);

        /* @var $command \CrazyCat\Framework\App\Setup\Config */
        $command = $this->objectManager->create(Config::class);
        $command->setApplication($consoleApplication);
        $command->run($consoleInput, $consoleOutput);
    }

    /**
     * @return void
     */
    private function setupFromHttp()
    {
        exit('Please run `index.php` in CLI to complete the setup.');
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function launch()
    {
        $appSourceDirs = [
            DIR_APP . DS . AppConfig::DIR,
            DIR_APP . DS . ComponentManager::DIR_APP_MODULES,
            DIR_APP . DS . ComponentManager::DIR_APP_THEMES
        ];
        foreach ($appSourceDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        if ($this->area->isCli()) {
            $this->setupFromCli();
        } else {
            $this->setupFromHttp();
        }
    }
}
