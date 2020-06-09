<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Files extends AbstractCache
{
    public const DIR = 'cache';

    /**
     * @return void
     */
    protected function init()
    {
        $file = DIR_VAR . DS . self::DIR . DS . $this->name;
        $this->data = is_file($file) ? json_decode(file_get_contents($file), true) : [];
    }

    /**
     * @return void
     */
    public function save()
    {
        if ($this->isEnabled) {
            if (!is_dir(DIR_VAR . DS . self::DIR)) {
                mkdir(DIR_VAR . DS . self::DIR, 0755, true);
            }
            file_put_contents(DIR_VAR . DS . self::DIR . DS . $this->name, json_encode($this->data));
        }
    }

    /**
     * @return void
     */
    public function clear()
    {
        if ($this->isEnabled && is_file(DIR_VAR . DS . self::DIR . DS . $this->name)) {
            unlink(DIR_VAR . DS . self::DIR . DS . $this->name);
        }
    }
}
