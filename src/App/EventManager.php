<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class EventManager {

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( ObjectManager $objectManager )
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $eventName
     * @param string $observer
     */
    public function addEvent( $eventName, $observer )
    {
        if ( !isset( $this->events[$eventName] ) ) {
            $this->events[$eventName] = [];
        }
        $this->events[$eventName][] = $observer;
    }

    /**
     * @param string $eventName
     * @param array $data
     */
    public function dispatch( $eventName, array $data = [] )
    {
        if ( !empty( $this->events[$eventName] ) ) {
            foreach ( $this->events[$eventName] as $observer ) {
                $this->objectManager->create( $observer )->execute( $data );
            }
        }
    }

}
