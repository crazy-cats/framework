<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Data\DataObject;
use Exception;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Config
{
    public const DIR = 'config';
    public const FILE = 'env.php';

    /**
     * @var \CrazyCat\Framework\Utility\ArrayTools
     */
    private $arrayTools;

    /**
     * @var array|mixed
     */
    private $settings;

    public function __construct(
        \CrazyCat\Framework\Utility\ArrayTools $arrayTools
    ) {
        $this->arrayTools = $arrayTools;
        $this->settings = (require DIR_APP . DS . self::DIR . DS . self::FILE) ?: [];
    }

    /**
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    public function getValue($path)
    {
        return $this->settings[$path] ?? null;
    }

    /**
     * @return void
     */
    public function save()
    {
        file_put_contents(
            DIR_APP . DS . Config::DIR . DS . self::CONFIG_FILE,
            sprintf("<?php\nreturn %s;\n", $this->arrayTools->arrayToString($this->settings))
        );
    }
}
