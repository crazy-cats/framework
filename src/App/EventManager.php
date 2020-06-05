<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Data\DataObject;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class EventManager
{
    const CACHE_NAME = 'events';
    const CONFIG_FILE = 'events.php';

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $eventName
     * @param string $observer
     */
    public function addEvent($eventName, $observer)
    {
        if (!isset($this->events[$eventName])) {
            $this->events[$eventName] = [];
        }
        if (!is_array($observer)) {
            $observer = [$observer];
        }
        $this->events[$eventName] = array_merge($this->events[$eventName], $observer);
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param string $eventName
     * @param array  $data
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function dispatch($eventName, array $data = [])
    {
        if (!empty($this->events[$eventName])) {
            profile_start('Event: ' . $eventName);
            foreach (array_unique($this->events[$eventName]) as $observer) {
                $this->objectManager->create($observer)->execute(new DataObject($data));
            }
            profile_end('Event: ' . $eventName);
        }
    }
}
