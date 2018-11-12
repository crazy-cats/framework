<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Handler;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Logger;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class ExceptionHandler {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Logger
     */
    private $logger;

    public function __construct( Area $area, Logger $logger )
    {
        $this->area = $area;
        $this->logger = $logger;
    }

    /**
     * @param \Exception $exception
     */
    private function processCliException( $exception )
    {
        echo $exception->getMessage() . "\n" . $exception->getTraceAsString();
    }

    /**
     * @param \Exception $exception
     */
    private function processHttpException( $exception )
    {
        echo $exception->getMessage() . "\n" . $exception->getTraceAsString();
    }

    /**
     * @param \Exception $exception
     */
    public function process( $exception )
    {
        return $this->area->isCli() ?
                $this->processCliException( $exception ) :
                $this->processHttpException( $exception );
    }

}
