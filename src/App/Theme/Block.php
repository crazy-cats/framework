<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

use CrazyCat\Framework\App\EventManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Block extends \CrazyCat\Framework\Data\Object {

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $template;

    public function __construct( EventManager $eventManager, array $data = [] )
    {
        parent::__construct( $data );

        $this->eventManager = $eventManager;
    }

    public function toHtml()
    {
        
    }

}
