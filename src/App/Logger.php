<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Processor;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Logger {

    const DIR = DIR_VAR . DS . 'log';

    /**
     * @var \Monolog\Handler\StreamHandler[]
     */
    protected $handlers = [];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Monolog\Logger
     */
    protected $processor;

    public function __construct( ObjectManager $objectManager )
    {
        $this->objectManager = $objectManager;
        $this->processor = $objectManager->create( Processor::class, [ 'name' => 'CrazyCat' ] );
    }

    public function log( $content, $file = 'system.log' )
    {
        if ( !isset( $this->handlers[$file] ) ) {
            $dir = self::DIR . DS . dirname( $file );
            if ( !is_dir( $dir ) ) {
                mkdir( $dir, 0755, true );
            }
            $this->handlers[$file] = $this->objectManager->create( StreamHandler::class, [
                'stream' => self::DIR . DS . $file,
                'level' => Processor::INFO ] );
            $this->processor->pushHandler( $this->handlers[$file] );
        }
        $this->processor->addInfo( print_r( $content, true ) );
    }

}
