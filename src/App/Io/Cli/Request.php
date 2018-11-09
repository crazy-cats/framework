<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Cli;

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

    public function __construct( Area $area, ModuleManager $moduleManager, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Cli\Response
     */
    public function process()
    {
        $response = $this->objectManager->create( Response::class );
        $this->area->setCode( Area::CODE_CLI );

        $consoleApplication = $this->objectManager->create( ConsoleApplication::class );
        foreach ( $this->moduleManager->getEnabledModules() as $module ) {
            foreach ( $module->getControllerActions( Area::CODE_CLI ) as $route => $className ) {
                $controllerAction = $this->objectManager->create( $className );
                $command = $this->objectManager->create( Command::class, $route )
                        ->setCode( [ $controllerAction, 'execute' ] );
                $consoleApplication->add( $command );
            }
        }
        $consoleApplication->run();

        return $response;
    }

}
