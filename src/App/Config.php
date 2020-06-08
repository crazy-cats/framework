<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Db\MySql;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Config extends \CrazyCat\Framework\App\Data\DataObject
{
    const DIR = 'config';
    const FILE = 'env.php';

    const SCOPE_GLOBAL = 'global';

    public function __construct()
    {
        parent::__construct([self::SCOPE_GLOBAL => require DIR_APP . DS . self::DIR . DS . self::FILE]);
    }

    /**
     * @param string $path
     * @return mixed
     * @throws \Exception
     */
    public function getValue($path)
    {
        $config = $this->getData(self::SCOPE_GLOBAL);
        return $config[$path] ?? null;
    }
}
