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
class Registry
{
    /**
     * @var array
     */
    private $contents = [];

    /**
     * @param string $name
     * @param mixed  $content
     * @return $this;
     * @throws \Exception
     */
    public function register($name, $content)
    {
        if (isset($this->contents[$name])) {
            throw new \Exception(sprintf('Content with name `%s` has been registered.', $name));
        }
        $this->contents[$name] = $content;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function registry($name)
    {
        return isset($this->contents[$name]) ? $this->contents[$name] : null;
    }
}
