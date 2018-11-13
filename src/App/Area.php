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
class Area {

    const CODE_GLOBAL = 'global';
    const CODE_CLI = 'cli';
    const CODE_CRON = 'cron';
    const CODE_API = 'api';
    const CODE_BACKEND = 'backend';
    const CODE_FRONTEND = 'frontend';

    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $allowedCodes = [
        self::CODE_CLI, self::CODE_CRON,
        self::CODE_API, self::CODE_BACKEND, self::CODE_FRONTEND
    ];

    /**
     * @return boolean
     */
    public function isCli()
    {
        return in_array( PHP_SAPI, [ 'cli' ] );
    }

    /**
     * @param string $code
     * @return boolean
     */
    public function verifyCode( $code )
    {
        return in_array( $code, $this->allowedCodes );
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
     * @param string $code
     */
    public function setCode( $code )
    {
        if ( !$this->verifyCode( $code ) ||
                ($this->isCli() && in_array( $code, [ self::CODE_FRONTEND, self::CODE_BACKEND ] ) ) ) {
            throw new \Exception( 'Invalidated area code.' );
        }

        $this->code = $code;
    }

}
