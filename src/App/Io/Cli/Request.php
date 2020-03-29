<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Cli;

use CrazyCat\Framework\App;
use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Component\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Request extends \CrazyCat\Framework\App\Io\AbstractRequest
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function process()
    {
        $this->area->setCode(Area::CODE_CLI);
        $this->moduleManager->collectConfig(Area::CODE_CLI);

        /* @var $consoleApplication \Symfony\Component\Console\Application */
        $consoleApplication = $this->objectManager->create(
            ConsoleApplication::class,
            [
                'name'    => 'CrazyCat CLI',
                'version' => App::getInstance()->getVersion()
            ]
        );

        foreach ($this->moduleManager->getEnabledModules() as $module) {
            foreach ($module->getControllerActions(Area::CODE_CLI) as $route => $className) {
                /* @var $command \Symfony\Component\Console\Command\Command */
                $command = $this->objectManager->create(Command::class, ['name' => str_replace('/', ':', $route)]);

                /* @var $controllerAction \CrazyCat\Framework\App\Component\Module\Controller\Cli\AbstractAction */
                $controllerAction = $this->objectManager->create($className);
                $controllerAction->setCommand($command)->init();

                $consoleApplication->add($command->setCode([$controllerAction, 'execute']));
            }
        }
        $consoleApplication->run();
    }
}
