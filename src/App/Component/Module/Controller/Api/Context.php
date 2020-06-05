<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Api;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Context extends \CrazyCat\Framework\App\Component\Module\Controller\AbstractContext
{
    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Logger $logger,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        parent::__construct($area, $config, $eventManager, $logger, $objectManager);
    }
}
