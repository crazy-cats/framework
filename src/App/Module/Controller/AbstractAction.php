<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( Area $area, EventManager $eventManager, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->eventManager->dispatch( 'controller_execute_before', [ 'action' => $this ] );
        $this->eventManager->dispatch( sprintf( '%s_controller_execute_before', $this->area->getCode() ), [ 'action' => $this ] );
        $this->eventManager->dispatch( sprintf( '%s_%s_%s_execute_before', $this->request->getRouteName(), $this->request->getControllerName(), $this->request->getActionName() ), [ 'action' => $this ] );
    }

}
