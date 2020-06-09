<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Area
{
    public const CODE_GLOBAL = 'global';
    public const CODE_CLI = 'cli';
    public const CODE_CRON = 'cron';
    public const CODE_API = 'api';
    public const CODE_BACKEND = 'backend';
    public const CODE_FRONTEND = 'frontend';

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $code = self::CODE_GLOBAL;

    /**
     * @var string
     */
    private $hashCode;

    /**
     * @var array
     */
    private $allowedCodes = [
        self::CODE_CLI,
        self::CODE_CRON,
        self::CODE_API,
        self::CODE_BACKEND,
        self::CODE_FRONTEND
    ];

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return bool
     */
    public function isCli()
    {
        return in_array(PHP_SAPI, ['cli']);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function verifyCode($code)
    {
        return in_array($code, $this->allowedCodes);
    }

    /**
     * @return string[]
     */
    public function getAllowedCodes()
    {
        return $this->allowedCodes;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getHashCode()
    {
        if (!isset($this->hashCode[$this->code])) {
            $this->hashCode[$this->code] = md5($this->code);
        }
        return $this->hashCode[$this->code];
    }

    /**
     * @param string $code
     * @throws \Exception
     */
    public function setCode($code)
    {
        if (!$this->verifyCode($code) ||
            ($this->isCli() && in_array($code, [self::CODE_FRONTEND, self::CODE_BACKEND]))) {
            throw new \Exception('Invalidated area code.');
        }

        $this->code = $code;
        $this->eventManager->dispatch('set_area_code_after', ['area' => $this]);
    }
}
