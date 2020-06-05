<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Cookies
{
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

    /**
     * @param Config                              $config
     * @param \CrazyCat\Framework\App\Io\Http\Url $url
     * @param Area                                $area
     * @throws \Exception
     */
    public function __construct(Config $config, Url $url, Area $area)
    {
        $baseUrl = $url->getBaseUrl();
        if ($area->getCode() == Area::CODE_BACKEND) {
            $baseUrl = $baseUrl . $config->getValue(Area::CODE_BACKEND)['route'] . '/';
        }
        $urlInfo = parse_url($baseUrl);

        $this->duration = $config->getValue($area->getCode())['cookies']['duration'];
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
    public function getData($key)
    {
        $cookies = filter_input_array(INPUT_COOKIE);
        return isset($cookies[$key]) ? $cookies[$key] : null;
    }

    /**
     * @param string   $key
     * @param string   $value
     * @param int|null $duration
     */
    public function setData($key, $value, $duration = null)
    {
        setcookie($key, $value, time() + ($duration ? $duration : $this->duration), $this->path, $this->domain);
    }

    /**
     * @param string $key
     */
    public function unsetData($key)
    {
        setcookie($key, 'deleted', time() - 31536000, '/', $this->domain);
    }
}
