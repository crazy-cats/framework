<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Frontend;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractAction {

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
