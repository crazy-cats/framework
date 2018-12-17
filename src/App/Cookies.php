<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Cookies {

    /**
     * @var int
     */
    protected $duration;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $path;

    public function __construct( Config $config, Url $url, Area $area )
    {
        $baseUrl = $url->getBaseUrl();
        if ( $area->getCode() == Area::CODE_BACKEND ) {
            $baseUrl = $baseUrl . $config->getData( Area::CODE_BACKEND )['route'] . '/';
        }
        $urlInfo = parse_url( $baseUrl );

        $this->duration = $config->getData( $area->getCode() )['cookies']['duration'];
        $this->domain = $urlInfo['host'];
        $this->path = $urlInfo['path'];
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getData( $key )
    {
        $cookies = filter_input_array( INPUT_COOKIE );
        return isset( $cookies[$key] ) ? $cookies[$key] : null;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $duration
     */
    public function setData( $key, $value, $duration = null )
    {
        setcookie( $key, $value, time() + ( $duration ? $duration : $this->duration ), $this->path, $this->domain );
    }

    /**
     * @param string $key
     */
    public function unsetData( $key )
    {
        setcookie( $key, 'deleted', time() - 31536000, '/', $this->domain );
    }

}
