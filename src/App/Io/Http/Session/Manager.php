<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Session;

use CrazyCat\Framework\App\Area;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    const SESSION_NAME = 'SID';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Cookies
     */
    private $cookies;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $request;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\Io\Http\Cookies $cookies,
        \CrazyCat\Framework\App\Io\Http\Request $request,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->config = $config;
        $this->cookies = $cookies;
        $this->objectManager = $objectManager;
        $this->request = $request;
    }

    /**
     * @return string
     */
    private function generateSessionId()
    {
        $sessionId = $this->request->getParam(self::SESSION_NAME) ?: $this->cookies->getData(self::SESSION_NAME);
        if ($sessionId === null || strpos($sessionId, $this->area->getHashCode()) !== 0) {
            return uniqid($this->area->getHashCode());
        }
        return $sessionId;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function init()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $config = $this->config->getValue(Area::CODE_GLOBAL)['session'];

        switch ($config['type']) {
            case SaveHandler\Files::TYPE:
                $saveHandler = SaveHandler\Files::class;
                break;

            case SaveHandler\Memcache::TYPE:
                $saveHandler = SaveHandler\Memcache::class;
                break;

            case SaveHandler\Files::TYPE:
                $saveHandler = SaveHandler\Database::class;
                break;

            case SaveHandler\Redis::TYPE:
                $saveHandler = SaveHandler\Redis::class;
                break;

            default:
                throw new \Exception('Invalidated session type.');
        }

        session_set_save_handler(
            $this->objectManager->get($saveHandler, ['config' => $config, 'areaCode' => $this->area->getCode()])
        );
        session_set_cookie_params(
            $this->cookies->getDuration(),
            $this->cookies->getPath(),
            $this->cookies->getDomain()
        );
        session_name(self::SESSION_NAME);
        session_id($this->generateSessionId());
        session_start();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }
}
