<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Db;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    const CONFIG_KEY = 'db';

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Db\Connection[]
     */
    private $connections = [];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(Config $config, ObjectManager $objectManager)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $name
     * @return \CrazyCat\Framework\App\Db\AbstractAdapter
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getConnection($name = 'default')
    {
        if (!isset($this->connections[$name])) {
            $dbSource = $this->config->getValue(Area::CODE_GLOBAL)['db'];
            if (!isset($dbSource[$name])) {
                throw new \Exception('Specified database connection does not exist.');
            }
            switch ($dbSource[$name]['type']) {
                case MySql::TYPE:
                    $this->connections[$name] = $this->objectManager->create(
                        MySql::class,
                        ['config' => $dbSource[$name]]
                    );
                    break;

                default:
                    throw new \Exception('Incorrect database type.');
            }
        }
        return $this->connections[$name];
    }

}
