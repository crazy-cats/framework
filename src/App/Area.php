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
     * @return boolean
     */
    public function isHttp()
    {
        return in_array( PHP_SAPI, [ 'cli' ] );
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
        if ( !in_array( $code, [ self::CODE_CLI, self::CODE_CRON, self::CODE_BACKEND, self::CODE_FRONTEND ] ) ||
                (!$this->isHttp() && in_array( $code, [ self::CODE_FRONTEND, self::CODE_BACKEND ] ) ) ) {
            throw new \Exception( 'Invalidated area code.' );
        }

        $this->code = $code;
    }

}
