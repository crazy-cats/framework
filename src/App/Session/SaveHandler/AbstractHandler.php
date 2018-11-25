<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session\SaveHandler;

/**
 * Description of AbstractHandler
 *
 * @author Bruce
 */
abstract class AbstractHandler implements \SessionHandlerInterface {

    /**
     * @var string
     */
    protected $areaCode;

    /**
     * @var array
     */
    protected $config;

    public function __construct( $config, $areaCode )
    {
        $this->areaCode = $areaCode;
        $this->config = $config;

        $this->init();
    }

    abstract protected function init();
}
