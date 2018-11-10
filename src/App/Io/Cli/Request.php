<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Cli;

use CrazyCat\Framework\App;
use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Request extends \CrazyCat\Framework\App\Io\AbstractRequest {

    /**
     * @var \CrazyCat\Framework\App
     */
    protected $app;

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( App $app, Area $area, ModuleManager $moduleManager, ObjectManager $objectManager )
    {
        $this->app = $app;
        $this->area = $area;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return void
     */
    public function process()
    {
        $this->area->setCode( Area::CODE_CLI );

        /* @var $consoleApplication \Symfony\Component\Console\Application */
        $consoleApplication = $this->objectManager->create( ConsoleApplication::class, [
            'name' => 'CrazyCat CLI',
            'version' => $this->app->getVersion() ] );

        foreach ( $this->moduleManager->getEnabledModules() as $module ) {
            foreach ( $module->getControllerActions( Area::CODE_CLI ) as $route => $className ) {

                /* @var $command \Symfony\Component\Console\Command\Command */
                $command = $this->objectManager->create( Command::class, [ 'name' => str_replace( '/', ':', $route ) ] );

                /* @var $controllerAction \CrazyCat\Framework\App\Module\Controller\Cli\AbstractAction */
                $controllerAction = $this->objectManager->create( $className );
                $controllerAction->setCommand( $command )->configure();

                $consoleApplication->add( $command->setCode( [ $controllerAction, 'execute' ] ) );
            }
        }
        $consoleApplication->run();
    }

}
