<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractBlock extends \CrazyCat\Framework\App\Theme\Block {

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    public function __construct( ModuleManager $moduleManager, EventManager $eventManager, array $data = [] )
    {
        parent::__construct( $eventManager, $data );

        $this->moduleManager = $moduleManager;
    }

    protected function getViewDir()
    {
        echo static::class;

        return $this->moduleManager->getModule( $namespace )->getData( 'dir' ) . DS . 'view' . DS . $this->area;
    }

}
