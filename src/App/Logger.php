<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Processor;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Logger
{
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

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->processor = $objectManager->create(Processor::class, ['name' => 'CrazyCat']);
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @param int    $level
     * @return void
     * @throws \ReflectionException
     */
    public function log($content, $file = 'system.log', $level = Processor::INFO)
    {
        if (!isset($this->handlers[$file])) {
            $dir = self::DIR . DS . dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $this->handlers[$file] = $this->objectManager->create(
                StreamHandler::class,
                ['stream' => self::DIR . DS . $file]
            );
            $this->processor->pushHandler($this->handlers[$file]);
        }
        $this->processor->addRecord($level, print_r($content, true));
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @return void
     * @throws \ReflectionException
     */
    public function addAlert($content, $file = 'system.log')
    {
        $this->log($content, $file, Processor::ALERT);
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @return void
     * @throws \ReflectionException
     */
    public function addCritical($content, $file = 'system.log')
    {
        $this->log($content, $file, Processor::CRITICAL);
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @return void
     * @throws \ReflectionException
     */
    public function addError($content, $file = 'system.log')
    {
        $this->log($content, $file, Processor::ERROR);
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @return void
     * @throws \ReflectionException
     */
    public function addWarning($content, $file = 'system.log')
    {
        $this->log($content, $file, Processor::WARNING);
    }

    /**
     * @param mixed  $content
     * @param string $file
     * @return void
     * @throws \ReflectionException
     */
    public function addDebug($content, $file = 'system.log')
    {
        $this->log($content, $file, Processor::DEBUG);
    }
}
