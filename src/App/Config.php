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
class Config extends \CrazyCat\Framework\App\Data\DataObject
{
    const DIR = 'config';
    const FILE = 'env.php';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    public function __construct(
        \CrazyCat\Framework\App\Area $area
    ) {
        parent::__construct(require DIR_APP . DS . self::DIR . DS . self::FILE);

        $this->area = $area;
    }

    /**
     * @param string      $path
     * @param string|null $scope
     * @return mixed
     */
    public function getValue($path, $scope = Area::CODE_GLOBAL)
    {
        $globalConfig = $this->getData(Area::CODE_GLOBAL);
        if ($scope == Area::CODE_GLOBAL) {
            return $globalConfig[$path] ?? null;
        }

        $config = $this->getData($scope);
        return $config[$path] ?? ($globalConfig[$path] ?? null);
    }
}
