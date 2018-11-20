<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Frontend;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractAction {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    public function __construct( Request $request, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $eventManager, $objectManager );

        $this->request = $request;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->eventManager->dispatch( 'controller_execute_before', [ 'action' => $this ] );

        $this->run();
    }

    /**
     * @return void
     */
    abstract protected function run();
}
